<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        [$start, $end] = $this->getPeriodDates();

        $expenses = (float) Transaction::expenses()
            ->whereBetween('date', [$start, $end])
            ->sum('amount');

        $previousStart = $start->subDays($start->diffInDays($end) + 1);
        $previousEnd = $start->subDay()->endOfDay();

        $previousExpenses = (float) Transaction::expenses()
            ->whereBetween('date', [$previousStart, $previousEnd])
            ->sum('amount');

        $dailyExpenses = Transaction::expenses()
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy(fn ($t) => $t->date->format('Y-m-d'))
            ->map(fn ($group) => (float) $group->sum('amount'))
            ->toArray();

        $chart = [];
        $cursor = $start;
        while ($cursor->lte($end)) {
            $chart[] = (float) ($dailyExpenses[$cursor->format('Y-m-d')] ?? 0);
            $cursor = $cursor->addDay();
        }

        $currentAccount = Account::where('name', 'Current')->first();
        $savingsAccount = Account::where('name', 'Savings')->first();

        return [
            Stat::make('Current', number_format((float) $currentAccount?->balance, 2).' EUR')
                ->icon('heroicon-o-credit-card')
                ->color('primary'),

            Stat::make('Savings', number_format((float) $savingsAccount?->balance, 2).' EUR')
                ->icon('heroicon-o-building-library')
                ->color('success'),

            Stat::make('Expenses', number_format($expenses, 2).' EUR')
                ->description($this->comparisonDescription($expenses, $previousExpenses))
                ->descriptionIcon($this->comparisonIcon($expenses, $previousExpenses))
                ->color('danger')
                ->chart($chart),
        ];
    }

    private function comparisonDescription(float $current, float $previous): string
    {
        if ($previous == 0) {
            return 'No data in previous period';
        }

        $change = (($current - $previous) / $previous) * 100;

        return number_format(abs($change), 1).'% '.($change >= 0 ? 'increase' : 'decrease');
    }

    private function comparisonIcon(float $current, float $previous): string
    {
        if ($previous == 0) {
            return 'heroicon-m-minus';
        }

        return $current >= $previous
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down';
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function getPeriodDates(): array
    {
        $period = $this->pageFilters['period'] ?? 'current_month';
        $now = CarbonImmutable::now();

        if ($period === 'custom') {
            $start = $this->pageFilters['startDate'] ?? null;
            $end = $this->pageFilters['endDate'] ?? null;

            if ($start && $end) {
                return [CarbonImmutable::parse($start)->startOfDay(), CarbonImmutable::parse($end)->endOfDay()];
            }
        }

        return match ($period) {
            'last_month' => [$now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()],
            'last_3_months' => [$now->subMonths(3)->startOfMonth(), $now->endOfMonth()],
            'last_6_months' => [$now->subMonths(6)->startOfMonth(), $now->endOfMonth()],
            'current_year' => [$now->startOfYear(), $now->endOfMonth()],
            default => [$now->startOfMonth(), $now->endOfMonth()],
        };
    }
}
