<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountBalancesOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Account::all()->map(function (Account $account) {
            $balance = (float) $account->balance;

            return Stat::make($account->icon.' '.$account->name, number_format($balance, 2).' EUR')
                ->color($balance >= 0 ? 'success' : 'danger');
        })->toArray();
    }
}
