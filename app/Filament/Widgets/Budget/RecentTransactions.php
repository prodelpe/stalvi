<?php

namespace App\Filament\Widgets\Budget;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactions extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    protected static bool $isDiscovered = false;

    public function table(Table $table): Table
    {
        $now = CarbonImmutable::now();

        return $table
            ->heading('This month\'s variable expenses')
            ->query(
                Transaction::query()
                    ->with('category.parent')
                    ->where('type', TransactionType::Expense)
                    ->whereHas('category', fn ($q) => $q->where('is_fixed', false))
                    ->whereBetween('date', [$now->startOfMonth(), $now->endOfMonth()])
            )
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
}
