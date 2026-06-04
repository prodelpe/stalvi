<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class IncomeBreakdownChart extends ChartWidget
{
    protected ?string $heading = 'This month breakdown';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $now = CarbonImmutable::now();
        $start = $now->startOfMonth();
        $end = $now->endOfMonth();

        $monthlyIncome = (float) (Budget::first()?->monthly_income ?? 0);

        $fixed = (float) Transaction::expenses()
            ->whereBetween('date', [$start, $end])
            ->whereHas('category', fn ($q) => $q->where('is_fixed', true))
            ->sum('amount');

        $variable = (float) Transaction::expenses()
            ->whereBetween('date', [$start, $end])
            ->whereHas('category', fn ($q) => $q->where('is_fixed', false))
            ->sum('amount');

        $saved = (float) Transaction::expenses()
            ->whereBetween('date', [$start, $end])
            ->whereHas('category', fn ($q) => $q->where('name', 'To savings'))
            ->sum('amount');

        $remaining = max(0, $monthlyIncome - $fixed - $variable - $saved);

        return [
            'datasets' => [
                [
                    'data' => [
                        round($fixed, 2),
                        round($variable, 2),
                        round($saved, 2),
                        round($remaining, 2),
                    ],
                    'backgroundColor' => ['#ef4444', '#f97316', '#10b981', '#e5e7eb'],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['Fixed', 'Variable', 'Saved', 'Remaining'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '65%',
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
