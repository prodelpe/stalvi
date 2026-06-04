<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Support\Htmlable;

class TopCategoriesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    public function getHeading(): string|Htmlable|null
    {
        return 'Top categories · '.$this->getPeriodLabel();
    }

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDates();

        $results = Transaction::expenses()
            ->whereBetween('date', [$start, $end])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->join('categories as parents', 'categories.parent_id', '=', 'parents.id')
            ->whereNotIn('parents.name', ['Income', 'Transfers'])
            ->selectRaw('parents.name as label, parents.color, SUM(transactions.amount) as total')
            ->groupBy('parents.id', 'parents.name', 'parents.color')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $results->map(fn ($r) => round((float) $r->total, 2))->toArray(),
                    'backgroundColor' => $results->pluck('color')->toArray(),
                    'borderColor' => $results->pluck('color')->toArray(),
                ],
            ],
            'labels' => $results->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }

    private function getPeriodLabel(): string
    {
        return match ($this->pageFilters['period'] ?? 'last_3_months') {
            'last_month' => 'Last month',
            'last_3_months' => 'Last 3 months',
            'last_6_months' => 'Last 6 months',
            'current_year' => 'Current year',
            'custom' => 'Custom range',
            default => 'Current month',
        };
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
