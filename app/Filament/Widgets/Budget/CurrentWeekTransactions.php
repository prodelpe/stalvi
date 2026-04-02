<?php

namespace App\Filament\Widgets\Budget;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\BudgetService;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class CurrentWeekTransactions extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected static bool $isDiscovered = false;

    public function table(Table $table): Table
    {
        return $table
            ->heading('This week\'s expenses')
            ->query($this->getQuery())
            ->columns([
                TextColumn::make('date')
                    ->date(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->description(fn (Transaction $record): ?string => $record->category?->parent?->name),
                TextColumn::make('description')
                    ->placeholder('—'),
                TextColumn::make('amount')
                    ->money('EUR')
                    ->color('danger'),
            ])
            ->defaultSort('date', 'desc')
            ->paginated(false);
    }

    private function getQuery(): Builder
    {
        $account = Account::whereHas('outgoingAllocations', fn ($q) => $q->where('is_active', true))->first();

        if (! $account) {
            return Transaction::query()->whereRaw('1 = 0');
        }

        $service = new BudgetService;
        $data = $service->calculateForMonth($account, CarbonImmutable::now());

        if ($data['current_week_index'] === null) {
            return Transaction::query()->whereRaw('1 = 0');
        }

        $week = $data['weeks'][$data['current_week_index']];

        return Transaction::query()
            ->with('category.parent')
            ->where('account_id', $account->id)
            ->where('type', TransactionType::Expense)
            ->whereBetween('date', [$week['start'], $week['end']]);
    }
}
