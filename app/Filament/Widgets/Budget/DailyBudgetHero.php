<?php

namespace App\Filament\Widgets\Budget;

use App\Services\DailyBudgetService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DailyBudgetHero extends BaseWidget
{
    protected static ?int $sort = 0;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $result = app(DailyBudgetService::class)->calculate();

        if (! $result) {
            return [
                Stat::make('Daily Budget', 'No active budget')
                    ->color('gray'),
            ];
        }

        $isPositive = $result->leftToday >= 0;

        return [
            Stat::make('Left today', number_format(abs($result->leftToday), 2).' EUR')
                ->description(
                    number_format($result->spentToday, 2).' spent today · '
                    .number_format($result->dailyAllowance, 2).' /day'
                    .($result->accumulated > 0 ? ' · +'.number_format($result->accumulated, 2).' saved' : '')
                )
                ->descriptionIcon($isPositive ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($isPositive ? 'success' : 'danger'),

            Stat::make('Income', number_format($result->monthlyIncome, 2).' EUR')
                ->description('Monthly income')
                ->color('info'),

            Stat::make('Fixed', number_format($result->fixedExpenses, 2).' EUR')
                ->description('Fixed expenses this month')
                ->color('warning'),

            Stat::make('Available', number_format($result->availableMonth, 2).' EUR')
                ->description(number_format($result->variableExpenses, 2).' EUR variable spent')
                ->descriptionIcon($result->availableMonth - $result->variableExpenses >= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($result->availableMonth - $result->variableExpenses >= 0 ? 'success' : 'danger'),
        ];
    }
}
