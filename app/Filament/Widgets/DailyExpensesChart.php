<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DailyExpensesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Daily variable spending';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDates();

        $dailyTotals = Transaction::expenses()
            ->whereBetween('date', [$start, $end])
            ->whereHas('category', fn ($q) => $q->where('is_fixed', false))
            ->get()
            ->groupBy(fn ($t) => $t->date->format('Y-m-d'))
            ->map(fn ($group) => (float) $group->sum('amount'))
            ->toArray();

        $labels = [];
        $data = [];
        $current = $start;

        while ($current->lte($end)) {
            $key = $current->format('Y-m-d');
            $labels[] = $current->format('d M');
            $data[] = round((float) ($dailyTotals[$key] ?? 0), 2);
            $current = $current->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Expenses',
                    'data' => $data,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function getPeriodDates(): array
    {
        $period = $this->pageFilters['period'] ?? 'last_3_months';
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
