<?php

namespace App\Filament\Widgets\Budget;

use App\Models\Account;
use App\Services\BudgetService;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class WeeklyProgressChart extends ChartWidget
{
    protected ?string $heading = 'Weekly progress';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

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

        $labels = [];
        $budgets = [];
        $spent = [];
        $spentColors = [];

        foreach ($data['weeks'] as $index => $week) {
            $labels[] = 'Week '.($index + 1).' ('.$week['start']->format('M d').'–'.$week['end']->format('d').')';
            $budgets[] = round($week['budget'], 2);
            $spent[] = round($week['spent'], 2);
            $spentColors[] = $week['spent'] > $week['budget'] ? '#ef4444' : '#22c55e';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Budget',
                    'data' => $budgets,
                    'backgroundColor' => '#d1d5db',
                    'borderColor' => '#d1d5db',
                ],
                [
                    'label' => 'Spent',
                    'data' => $spent,
                    'backgroundColor' => $spentColors,
                    'borderColor' => $spentColors,
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
