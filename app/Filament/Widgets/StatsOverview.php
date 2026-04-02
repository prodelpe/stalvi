<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $currentStart = CarbonImmutable::now()->startOfMonth();
        $currentEnd = CarbonImmutable::now()->endOfMonth();
        $previousStart = $currentStart->subMonth();
        $previousEnd = $currentStart->subDay()->endOfDay();

        $currentExpenses = Transaction::expenses()
            ->whereBetween('date', [$currentStart, $currentEnd])
            ->sum('amount');

        $currentIncomes = Transaction::incomes()
            ->whereBetween('date', [$currentStart, $currentEnd])
            ->sum('amount');

        $previousExpenses = Transaction::expenses()
            ->whereBetween('date', [$previousStart, $previousEnd])
            ->sum('amount');

        $previousIncomes = Transaction::incomes()
            ->whereBetween('date', [$previousStart, $previousEnd])
            ->sum('amount');

        $balance = $currentIncomes - $currentExpenses;

        // Daily chart data for current month
        $dailyExpenses = Transaction::expenses()
            ->whereBetween('date', [$currentStart, $currentEnd])
            ->selectRaw('date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $dailyIncomes = Transaction::incomes()
            ->whereBetween('date', [$currentStart, $currentEnd])
            ->selectRaw('date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $daysInMonth = $currentStart->daysInMonth;
        $expenseChart = [];
        $incomeChart = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $day = $currentStart->day($i)->format('Y-m-d');
            $expenseChart[] = (float) ($dailyExpenses[$day] ?? 0);
            $incomeChart[] = (float) ($dailyIncomes[$day] ?? 0);
        }

        return [
            Stat::make('Expenses', number_format($currentExpenses, 2).' EUR')
                ->description($this->comparisonDescription($currentExpenses, $previousExpenses))
                ->descriptionIcon($this->comparisonIcon($currentExpenses, $previousExpenses))
                ->color('danger')
                ->chart($expenseChart),
            Stat::make('Income', number_format($currentIncomes, 2).' EUR')
                ->description($this->comparisonDescription($currentIncomes, $previousIncomes))
                ->descriptionIcon($this->comparisonIcon($currentIncomes, $previousIncomes))
                ->color('success')
                ->chart($incomeChart),
            Stat::make('Balance', number_format($balance, 2).' EUR')
                ->color($balance >= 0 ? 'success' : 'danger'),
        ];
    }

    private function comparisonDescription(float $current, float $previous): string
    {
        if ($previous == 0) {
            return 'No data last month';
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
}
