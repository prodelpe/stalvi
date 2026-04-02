<?php

namespace App\Filament\Widgets\Budget;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\BudgetService;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BudgetOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected ?string $pollingInterval = null;

    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $account = Account::whereHas('outgoingAllocations', fn ($q) => $q->where('is_active', true))->first();

        if (! $account) {
            return [
                Stat::make('Budget', 'No allocations configured')
                    ->color('gray'),
            ];
        }

        $service = new BudgetService;
        $now = CarbonImmutable::now();
        $data = $service->calculateForMonth($account, $now);

        $stats = [];

        // This week
        if ($data['current_week_index'] !== null) {
            $week = $data['weeks'][$data['current_week_index']];
            $overBudget = $week['remaining'] < 0;
            $chart = $this->getDailySpending($account, $week['start'], $week['end']);

            $stats[] = Stat::make('This week', number_format(abs($week['remaining']), 2).' EUR')
                ->description(number_format($week['spent'], 2).' of '.number_format($week['budget'], 2).' · '.($overBudget ? 'over budget' : 'remaining'))
                ->descriptionIcon($overBudget ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($overBudget ? 'danger' : 'success')
                ->chart($chart);
        }

        // This month
        $monthSpent = array_sum(array_column($data['weeks'], 'spent'));
        $monthRemaining = $data['monthly_budget'] - $monthSpent;
        $monthOver = $monthRemaining < 0;

        $stats[] = Stat::make('This month', number_format(abs($monthRemaining), 2).' EUR')
            ->description(number_format($monthSpent, 2).' of '.number_format($data['monthly_budget'], 2).' · '.($monthOver ? 'over budget' : 'remaining'))
            ->descriptionIcon($monthOver ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($monthOver ? 'danger' : 'success');

        // Last week
        if ($data['current_week_index'] !== null && $data['current_week_index'] > 0) {
            $lastWeek = $data['weeks'][$data['current_week_index'] - 1];
            $saved = $lastWeek['remaining'];

            $stats[] = Stat::make('Last week', number_format(abs($saved), 2).' EUR')
                ->description($saved >= 0 ? 'saved' : 'overspent')
                ->descriptionIcon($saved >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($saved >= 0 ? 'success' : 'danger');
        }

        // Monthly budget breakdown
        $stats[] = Stat::make('Monthly budget', number_format($data['monthly_budget'], 2).' EUR')
            ->description('Income '.number_format($data['monthly_income'], 2).' − Allocations '.number_format($data['total_allocations'], 2))
            ->color('info');

        return $stats;
    }

    /**
     * @return array<int, float>
     */
    private function getDailySpending(Account $account, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $dailyTotals = Transaction::expenses()
            ->where('account_id', $account->id)
            ->whereBetween('date', [$start, $end])
            ->selectRaw('date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $chart = [];
        $cursor = $start;
        while ($cursor->lte($end)) {
            $chart[] = (float) ($dailyTotals[$cursor->format('Y-m-d')] ?? 0);
            $cursor = $cursor->addDay();
        }

        return $chart;
    }
}
