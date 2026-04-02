<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Filament\Widgets\Budget\BudgetOverview;
use App\Filament\Widgets\Budget\CurrentWeekTransactions;
use App\Filament\Widgets\Budget\WeeklyExpensesByCategoryChart;
use App\Filament\Widgets\Budget\WeeklyProgressChart;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BudgetDashboard extends BaseDashboard
{
    protected static string $routePath = 'budget';

    protected static ?string $title = 'Budget';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = -1;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Dashboards;

    public function getWidgets(): array
    {
        return [
            BudgetOverview::class,
            WeeklyProgressChart::class,
            WeeklyExpensesByCategoryChart::class,
            CurrentWeekTransactions::class,
        ];
    }
}
