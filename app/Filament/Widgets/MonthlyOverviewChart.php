<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class MonthlyOverviewChart extends ChartWidget
{
    protected ?string $heading = 'Income vs Expenses';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $now = CarbonImmutable::now();
        $labels = [];
        $expenses = [];
        $incomes = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = $now->subMonths($i);
            $start = $month->startOfMonth();
            $end = $month->endOfMonth();

            $labels[] = $month->format('M Y');

            $expenses[] = round((float) Transaction::expenses()
                ->whereBetween('date', [$start, $end])
                ->sum('amount'), 2);

            $incomes[] = round((float) Transaction::incomes()
                ->whereBetween('date', [$start, $end])
                ->sum('amount'), 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => $incomes,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expenses,
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#ef4444',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
