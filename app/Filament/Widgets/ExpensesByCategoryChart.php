<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class ExpensesByCategoryChart extends ChartWidget
{
    use HasFiltersSchema;
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    public function getHeading(): string|Htmlable|null
    {
        $parentCategoryId = $this->filters['parentCategory'] ?? null;

        if ($parentCategoryId) {
            $parent = Category::find($parentCategoryId);

            return 'Expenses: '.($parent?->icon.' '.$parent?->name);
        }

        $viewMode = $this->filters['viewMode'] ?? 'parent';

        return $viewMode === 'subcategory'
            ? 'Expenses by subcategory'
            : 'Expenses by category';
    }

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('viewMode')
                ->label('Group by')
                ->options([
                    'parent' => 'Parent category',
                    'subcategory' => 'All subcategories',
                ])
                ->default('parent')
                ->live(),
            Select::make('parentCategory')
                ->label('Drill into')
                ->placeholder('All categories')
                ->options(fn (): array => Category::roots()
                    ->where('name', '!=', 'Income')
                    ->pluck('name', 'id')
                    ->toArray())
                ->nullable()
                ->visible(fn (callable $get): bool => $get('viewMode') !== 'subcategory'),
        ]);
    }

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDates();

        $viewMode = $this->filters['viewMode'] ?? 'parent';
        $parentCategoryId = $this->filters['parentCategory'] ?? null;

        if ($parentCategoryId) {
            $data = $this->getSubcategoryData($start, $end, (int) $parentCategoryId);
        } elseif ($viewMode === 'subcategory') {
            $data = $this->getSubcategoryData($start, $end);
            $data = $this->applyColorShading($data);
        } else {
            $data = $this->getParentData($start, $end);
        }

        return [
            'datasets' => [
                [
                    'data' => $data->pluck('total')->map(fn ($v) => round((float) $v, 2))->toArray(),
                    'backgroundColor' => $data->pluck('color')->toArray(),
                ],
            ],
            'labels' => $data->pluck('label')->toArray(),
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

    private function getParentData(CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return Transaction::expenses()
            ->whereBetween('date', [$start, $end])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->join('categories as parents', 'categories.parent_id', '=', 'parents.id')
            ->selectRaw('parents.name as label, parents.color, parents.icon, SUM(transactions.amount) as total')
            ->groupBy('parents.id', 'parents.name', 'parents.color', 'parents.icon')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $row->label = $row->icon.' '.$row->label;

                return $row;
            });
    }

    private function getSubcategoryData(CarbonImmutable $start, CarbonImmutable $end, ?int $parentId = null): Collection
    {
        return Transaction::expenses()
            ->whereBetween('date', [$start, $end])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->join('categories as parents', 'categories.parent_id', '=', 'parents.id')
            ->when($parentId, fn ($q) => $q->where('categories.parent_id', $parentId))
            ->selectRaw('categories.name as label, parents.color, parents.name as parent_name, SUM(transactions.amount) as total')
            ->groupBy('categories.id', 'categories.name', 'parents.color', 'parents.name')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Apply brightness variations so subcategories sharing a parent color are distinguishable.
     */
    private function applyColorShading(Collection $data): Collection
    {
        $grouped = $data->groupBy('color');

        return $grouped->flatMap(function (Collection $items) {
            $count = $items->count();
            if ($count <= 1) {
                return $items;
            }

            return $items->values()->map(function ($item, int $index) use ($count) {
                $factor = 1 - ($index * 0.3 / max($count - 1, 1));
                $item->color = $this->adjustBrightness($item->color, $factor);

                return $item;
            });
        })->sortByDesc('total')->values();
    }

    private function adjustBrightness(string $hex, float $factor): string
    {
        $hex = ltrim($hex, '#');
        $r = min(255, (int) round(hexdec(substr($hex, 0, 2)) * $factor));
        $g = min(255, (int) round(hexdec(substr($hex, 2, 2)) * $factor));
        $b = min(255, (int) round(hexdec(substr($hex, 4, 2)) * $factor));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
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
