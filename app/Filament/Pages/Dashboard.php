<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use UnitEnum;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    protected static ?string $title = 'Overview';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Dashboards;

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->schema([
                    Select::make('period')
                        ->label('Period')
                        ->options([
                            'current_month' => 'Current month',
                            'last_month' => 'Last month',
                            'last_3_months' => 'Last 3 months',
                            'last_6_months' => 'Last 6 months',
                            'current_year' => 'Current year',
                            'custom' => 'Custom range',
                        ])
                        ->default('current_month')
                        ->live(),
                    DatePicker::make('startDate')
                        ->label('From')
                        ->visible(fn (callable $get): bool => $get('period') === 'custom')
                        ->required(fn (callable $get): bool => $get('period') === 'custom'),
                    DatePicker::make('endDate')
                        ->label('To')
                        ->visible(fn (callable $get): bool => $get('period') === 'custom')
                        ->required(fn (callable $get): bool => $get('period') === 'custom'),
                ]),
        ];
    }
}
