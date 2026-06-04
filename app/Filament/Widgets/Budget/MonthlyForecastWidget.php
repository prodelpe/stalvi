<?php

namespace App\Filament\Widgets\Budget;

use App\Services\DailyBudgetService;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonthlyForecastWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $result = app(DailyBudgetService::class)->calculate();

        if (! $result) {
            return [
                Stat::make('Forecast', 'No active budget')
                    ->color('gray'),
            ];
        }

        $now = CarbonImmutable::now();
        $daysElapsed = $now->day;

        $avgDailySpending = $daysElapsed > 0
            ? round($result->variableExpenses / $daysElapsed, 2)
            : 0.0;

        $forecastedVariable = round($avgDailySpending * $result->daysInMonth, 2);
        $forecastedSavings = round($result->monthlyIncome - $result->fixedExpenses - $forecastedVariable, 2);

        $forecastOverBudget = $forecastedVariable > $result->availableMonth;

        return [
            Stat::make('Avg daily spending', number_format($avgDailySpending, 2).' EUR')
                ->description('Based on '.$daysElapsed.' day'.($daysElapsed !== 1 ? 's' : '').' elapsed')
                ->color('info'),

            Stat::make('Month forecast', number_format($forecastedVariable, 2).' EUR')
                ->description('Projected variable spend by end of month')
                ->descriptionIcon($forecastOverBudget ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($forecastOverBudget ? 'danger' : 'success'),

            Stat::make('Savings forecast', number_format($forecastedSavings, 2).' EUR')
                ->description('Forecasted amount saved this month')
                ->descriptionIcon($forecastedSavings >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($forecastedSavings >= 0 ? 'success' : 'danger'),
        ];
    }
}
