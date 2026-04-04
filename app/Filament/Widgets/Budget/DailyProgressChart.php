<?php

namespace App\Filament\Widgets\Budget;

use App\Models\Transaction;
use App\Services\DailyBudgetService;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class DailyProgressChart extends ChartWidget
{
    protected ?string $heading = 'Daily spending';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected static bool $isDiscovered = false;

    protected function getData(): array
    {
        $now = CarbonImmutable::now();
        $monthStart = $now->startOfMonth();
        $monthEnd = $now->endOfMonth();

        $result = app(DailyBudgetService::class)->calculate($now);

        $dailyTarget = $result
            ? round($result->availableMonth / $result->daysInMonth, 2)
            : 0;

        $dailyTotals = Transaction::expenses()
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->whereHas('category', fn ($q) => $q->where('is_fixed', false))
            ->get()
            ->groupBy(fn ($t) => $t->date->format('Y-m-d'))
            ->map(fn ($group) => (float) $group->sum('amount'))
            ->toArray();

        $labels = [];
        $spending = [];
        $colors = [];
        $cursor = $monthStart;

        while ($cursor->lte($monthEnd)) {
            $labels[] = $cursor->format('j');
            $dayTotal = round((float) ($dailyTotals[$cursor->format('Y-m-d')] ?? 0), 2);
            $spending[] = $dayTotal;
            $colors[] = $dayTotal <= $dailyTarget ? '#22c55e' : '#ef4444';
            $cursor = $cursor->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Spent',
                    'data' => $spending,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                ],
                [
                    'label' => 'Daily target ('.number_format($dailyTarget, 2).' EUR)',
                    'data' => array_fill(0, count($labels), $dailyTarget),
                    'type' => 'line',
                    'borderColor' => '#9ca3af',
                    'borderDash' => [5, 5],
                    'borderWidth' => 2,
                    'pointRadius' => 0,
                    'fill' => false,
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
