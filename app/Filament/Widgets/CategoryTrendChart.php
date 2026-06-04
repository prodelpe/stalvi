<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class CategoryTrendChart extends ChartWidget
{
    protected ?string $heading = 'Spending by category';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $now = CarbonImmutable::now();

        $labels = [];
        for ($i = 11; $i >= 0; $i--) {
            $labels[] = $now->subMonths($i)->format('M Y');
        }

        $parentCategories = Category::roots()
            ->whereNotIn('name', ['Income', 'Transfers'])
            ->get();

        $rows = Transaction::expenses()
            ->whereBetween('date', [$now->subMonths(11)->startOfMonth(), $now->endOfMonth()])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->join('categories as parents', 'categories.parent_id', '=', 'parents.id')
            ->whereNotIn('parents.name', ['Income', 'Transfers'])
            ->selectRaw("parents.id as parent_id, strftime('%Y-%m', transactions.date) as month, SUM(transactions.amount) as total")
            ->groupBy('parents.id', 'month')
            ->get()
            ->groupBy('parent_id');

        $datasets = $parentCategories->map(function (Category $category) use ($now, $rows): array {
            $categoryRows = $rows->get($category->id, collect())->keyBy('month');
            $data = [];

            for ($i = 11; $i >= 0; $i--) {
                $monthKey = $now->subMonths($i)->format('Y-m');
                $data[] = round((float) ($categoryRows->get($monthKey)?->total ?? 0), 2);
            }

            return [
                'label' => $category->name,
                'data' => $data,
                'backgroundColor' => $category->color,
                'borderColor' => $category->color,
            ];
        })->values()->toArray();

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                ],
            ],
        ];
    }
}
