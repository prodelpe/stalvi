<?php

namespace App\Filament\Widgets\Budget;

use App\Models\Budget;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class SavingsRateChart extends ChartWidget
{
    protected ?string $heading = 'Savings rate';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected static bool $isDiscovered = false;

    protected function getData(): array
    {
        $now = CarbonImmutable::now();
        $monthlyIncome = (float) (Budget::first()?->monthly_income ?? 0);

        $labels = [];
        $rates = [];
        $reference = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = $now->subMonths($i);
            $start = $month->startOfMonth();
            $end = $month->endOfMonth();

            $labels[] = $month->format('M Y');

            $totalExpenses = (float) Transaction::expenses()
                ->whereBetween('date', [$start, $end])
                ->sum('amount');

            $rate = $monthlyIncome > 0
                ? round(($monthlyIncome - $totalExpenses) / $monthlyIncome * 100, 1)
                : 0;

            $rates[] = $rate;
            $reference[] = 20;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Savings rate',
                    'data' => $rates,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointRadius' => 3,
                ],
                [
                    'label' => 'Recommended (20%)',
                    'data' => $reference,
                    'borderColor' => '#9ca3af',
                    'borderDash' => [5, 5],
                    'borderWidth' => 2,
                    'pointRadius' => 0,
                    'fill' => false,
                    'backgroundColor' => 'transparent',
                    'type' => 'line',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => "function(value) { return value + '%'; }",
                    ],
                ],
            ],
        ];
    }
}
