<?php

namespace App\Filament\Widgets\Budget;

use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class MonthlyExpensesByCategoryChart extends ChartWidget
{
    protected ?string $heading = 'This month by category';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected static bool $isDiscovered = false;

    protected function getData(): array
    {
        $now = CarbonImmutable::now();
        $monthStart = $now->startOfMonth();
        $monthEnd = $now->endOfMonth();

        $expenses = Transaction::expenses()
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->whereHas('category', fn ($q) => $q->where('is_fixed', false))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->join('categories as parents', 'categories.parent_id', '=', 'parents.id')
            ->selectRaw('parents.name as parent_name, parents.color, parents.icon, SUM(transactions.amount) as total')
            ->groupBy('parents.id', 'parents.name', 'parents.color', 'parents.icon')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $expenses->pluck('total')->map(fn ($v) => round((float) $v, 2))->toArray(),
                    'backgroundColor' => $expenses->pluck('color')->toArray(),
                ],
            ],
            'labels' => $expenses->map(fn ($row) => $row->icon.' '.$row->parent_name)->toArray(),
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
        ];
    }
}
