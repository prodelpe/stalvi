<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ExpensesByCategoryChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Expenses by category';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDates();

        $data = Transaction::expenses()
            ->whereBetween('date', [$start, $end])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->join('categories as parents', 'categories.parent_id', '=', 'parents.id')
            ->selectRaw('parents.name as parent_name, parents.color, parents.icon, SUM(transactions.amount) as total')
            ->groupBy('parents.id', 'parents.name', 'parents.color', 'parents.icon')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $data->pluck('total')->map(fn ($v) => round((float) $v, 2))->toArray(),
                    'backgroundColor' => $data->pluck('color')->toArray(),
                ],
            ],
            'labels' => $data->map(fn ($row) => $row->icon . ' ' . $row->parent_name)->toArray(),
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

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function getPeriodDates(): array
    {
        $period = $this->pageFilters['period'] ?? 'current_month';
        $now = CarbonImmutable::now();

        if ($period === 'custom') {
            $start = $this->pageFilters['startDate'] ?? null;
            $end = $this->pageFilters['endDate'] ?? null;

            if ($start && $end) {
                return [CarbonImmutable::parse($start)->startOfDay(), CarbonImmutable::parse($end)->endOfDay()];
            }
        }

        return match ($period) {
            'last_month' => [$now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()],
            'last_3_months' => [$now->subMonths(3)->startOfMonth(), $now->endOfMonth()],
            'last_6_months' => [$now->subMonths(6)->startOfMonth(), $now->endOfMonth()],
            'current_year' => [$now->startOfYear(), $now->endOfMonth()],
            default => [$now->startOfMonth(), $now->endOfMonth()],
        };
    }
}
