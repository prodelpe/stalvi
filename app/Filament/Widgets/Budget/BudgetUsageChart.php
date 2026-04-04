<?php

namespace App\Filament\Widgets\Budget;

use App\Services\DailyBudgetService;
use Filament\Widgets\ChartWidget;

class BudgetUsageChart extends ChartWidget
{
    protected ?string $heading = 'Monthly budget usage';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected static bool $isDiscovered = false;

    protected function getData(): array
    {
        $result = app(DailyBudgetService::class)->calculate();

        if (! $result || $result->availableMonth <= 0) {
            return ['datasets' => [], 'labels' => []];
        }

        $spent = $result->variableExpenses;
        $remaining = max($result->remaining, 0);
        $over = $result->remaining < 0 ? abs($result->remaining) : 0;

        $data = [$spent, $remaining];
        $colors = ['#ef4444', '#22c55e'];
        $labels = ['Spent', 'Remaining'];

        if ($over > 0) {
            $data = [$result->availableMonth, $over];
            $colors = ['#ef4444', '#dc2626'];
            $labels = ['Budget', 'Over budget'];
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '70%',
        ];
    }
}
