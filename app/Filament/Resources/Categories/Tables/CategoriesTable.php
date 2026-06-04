<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('—'),
                ColorColumn::make('color'),
                IconColumn::make('is_fixed')
                    ->boolean()
                    ->label('Fixed'),
            ])
            ->filters([
                TernaryFilter::make('parent_id')
                    ->label('Type')
                    ->placeholder('All')
                    ->trueLabel('Only children')
                    ->falseLabel('Only parents')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('parent_id'),
                        false: fn (Builder $query): Builder => $query->whereNull('parent_id'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
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
