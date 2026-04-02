<?php

namespace App\Filament\Widgets\Budget;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\BudgetService;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class WeeklyExpensesByCategoryChart extends ChartWidget
{
    protected ?string $heading = 'This week by category';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected static bool $isDiscovered = false;

    protected function getData(): array
    {
        $account = Account::whereHas('outgoingAllocations', fn ($q) => $q->where('is_active', true))->first();

        if (! $account) {
            return ['datasets' => [], 'labels' => []];
        }

        $service = new BudgetService;
        $data = $service->calculateForMonth($account, CarbonImmutable::now());

        if ($data['current_week_index'] === null) {
            return ['datasets' => [], 'labels' => []];
        }

        $week = $data['weeks'][$data['current_week_index']];

        $expenses = Transaction::expenses()
            ->where('account_id', $account->id)
            ->whereBetween('date', [$week['start'], $week['end']])
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
