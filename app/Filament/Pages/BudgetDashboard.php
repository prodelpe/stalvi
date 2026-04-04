<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Filament\Widgets\Budget\BudgetUsageChart;
use App\Filament\Widgets\Budget\DailyBudgetHero;
use App\Filament\Widgets\Budget\DailyProgressChart;
use App\Filament\Widgets\Budget\MonthlyExpensesByCategoryChart;
use App\Filament\Widgets\Budget\RecentTransactions;
use App\Models\Budget;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BudgetDashboard extends BaseDashboard
{
    protected static string $routePath = 'budget';

    protected static ?string $title = 'Daily Budget';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = -1;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Dashboards;

    protected function getHeaderActions(): array
    {
        $budget = Budget::first();

        return [
            Action::make('editBudget')
                ->label('Edit budget')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->fillForm([
                    'monthly_income' => $budget?->monthly_income,
                ])
                ->form([
                    TextInput::make('monthly_income')
                        ->label('Monthly income')
                        ->numeric()
                        ->required()
                        ->prefix('EUR')
                        ->minValue(0),
                ])
                ->action(function (array $data): void {
                    $budget = Budget::first() ?? new Budget;
                    $budget->monthly_income = $data['monthly_income'];
                    $budget->save();
                }),
        ];
    }

    public function getWidgets(): array
    {
        return [
            DailyBudgetHero::class,
            BudgetUsageChart::class,
            MonthlyExpensesByCategoryChart::class,
            DailyProgressChart::class,
            RecentTransactions::class,
        ];
    }
}
