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

        $isPositive = $result->dailyBudget >= 0;

        return [
            Stat::make('Daily Budget', number_format(abs($result->dailyBudget), 2).' EUR')
                ->description($result->daysLeft.' days remaining'.($isPositive ? '' : ' · over budget'))
                ->descriptionIcon($isPositive ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($isPositive ? 'success' : 'danger'),

            Stat::make('Income', number_format($result->monthlyIncome, 2).' EUR')
                ->description('Monthly income')
                ->color('info'),

            Stat::make('Fixed', number_format($result->fixedExpenses, 2).' EUR')
                ->description('Fixed expenses this month')
                ->color('warning'),

            Stat::make('Remaining', number_format($result->remaining, 2).' EUR')
                ->description(number_format($result->variableExpenses, 2).' EUR variable spent')
                ->descriptionIcon($result->remaining >= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($result->remaining >= 0 ? 'success' : 'danger'),
        ];
    }
}
