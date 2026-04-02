<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Enums\TransactionType;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->description(fn ($record): ?string => $record->category?->parent?->name)
                    ->searchable(),
                TextColumn::make('description')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('EUR')
                    ->color(fn ($record): string => $record->type === TransactionType::Income ? 'success' : 'danger')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(TransactionType::class),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->multiple()
                    ->options(function (): array {
                        $grouped = [];

                        Category::roots()->with('children')->get()->each(function (Category $parent) use (&$grouped) {
                            $children = $parent->children->pluck('name', 'id')->toArray();
                            if ($children) {
                                $grouped[$parent->icon . ' ' . $parent->name] = $children;
                            }
                        });

                        return $grouped;
                    })
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
