<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class NetWorthChart extends ChartWidget
{
    protected ?string $heading = 'Net worth';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $now = CarbonImmutable::now();
        $totalInitialBalance = (float) Account::sum('initial_balance');

        $labels = [];
        $data = [];

        for ($i = 23; $i >= 0; $i--) {
            $monthEnd = $now->subMonths($i)->endOfMonth();
            $labels[] = $monthEnd->format('M Y');

            $net = (float) Transaction::selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as net")
                ->where('date', '<=', $monthEnd)
                ->value('net');

            $data[] = round($totalInitialBalance + $net, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Net worth',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
