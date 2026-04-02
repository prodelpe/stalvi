<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\CarbonImmutable;

class BudgetService
{
    /**
     * @return array{
     *     monthly_income: float,
     *     total_allocations: float,
     *     monthly_budget: float,
     *     weeks: array<int, array{
     *         start: CarbonImmutable,
     *         end: CarbonImmutable,
     *         days: int,
     *         budget: float,
     *         spent: float,
     *         carry: float,
     *         remaining: float
     *     }>,
     *     current_week_index: int|null
     * }
     */
    public function calculateForMonth(Account $account, CarbonImmutable $month): array
    {
        $monthStart = $month->startOfMonth();
        $monthEnd = $month->endOfMonth();
        $daysInMonth = $monthStart->daysInMonth;

        $monthlyIncome = (float) Transaction::incomes()
            ->where('account_id', $account->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->sum('amount');

        $totalAllocations = (float) $account->outgoingAllocations()->active()->sum('amount');

        $monthlyBudget = $monthlyIncome - $totalAllocations;

        $segments = $this->getWeekSegments($monthStart, $monthEnd);

        $carry = 0.0;
        $weeks = [];
        $currentWeekIndex = null;
        $today = CarbonImmutable::now()->startOfDay();

        foreach ($segments as $index => $segment) {
            $days = $segment['start']->diffInDays($segment['end']) + 1;
            $proportionalBudget = round(($days / $daysInMonth) * $monthlyBudget, 2);

            $spent = (float) Transaction::expenses()
                ->where('account_id', $account->id)
                ->whereBetween('date', [$segment['start'], $segment['end']])
                ->sum('amount');

            $effectiveBudget = $proportionalBudget + $carry;
            $remaining = $effectiveBudget - $spent;

            $weeks[] = [
                'start' => $segment['start'],
                'end' => $segment['end'],
                'days' => $days,
                'budget' => round($effectiveBudget, 2),
                'spent' => round($spent, 2),
                'carry' => round($carry, 2),
                'remaining' => round($remaining, 2),
            ];

            if ($today->gte($segment['start']) && $today->lte($segment['end'])) {
                $currentWeekIndex = $index;
            }

            $carry = $remaining;
        }

        return [
            'monthly_income' => round($monthlyIncome, 2),
            'total_allocations' => round($totalAllocations, 2),
            'monthly_budget' => round($monthlyBudget, 2),
            'weeks' => $weeks,
            'current_week_index' => $currentWeekIndex,
        ];
    }

    /**
     * @return array<int, array{start: CarbonImmutable, end: CarbonImmutable}>
     */
    private function getWeekSegments(CarbonImmutable $monthStart, CarbonImmutable $monthEnd): array
    {
        $segments = [];
        $cursor = $monthStart;

        while ($cursor->lte($monthEnd)) {
            $weekEnd = $cursor->endOfWeek(CarbonImmutable::SUNDAY);
            if ($weekEnd->gt($monthEnd)) {
                $weekEnd = $monthEnd;
            }

            $segments[] = ['start' => $cursor, 'end' => $weekEnd];
            $cursor = $weekEnd->addDay()->startOfDay();
        }

        return $segments;
    }
}
