<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTransactions extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with('category.parent')
                    ->latest('date')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('date')
                    ->date(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->description(fn (Transaction $record): ?string => $record->category?->parent?->name),
                TextColumn::make('description')
                    ->placeholder('—'),
                TextColumn::make('amount')
                    ->money('EUR')
                    ->color(fn (Transaction $record): string => $record->type === TransactionType::Income ? 'success' : 'danger'),
            ])
            ->paginated(false);
    }
}
