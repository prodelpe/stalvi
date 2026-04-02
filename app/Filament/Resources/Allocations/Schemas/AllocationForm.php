<?php

namespace App\Filament\Resources\Allocations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AllocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('source_account_id')
                    ->label('From account')
                    ->relationship('sourceAccount', 'name')
                    ->required()
                    ->preload(),
                Select::make('destination_account_id')
                    ->label('To account')
                    ->relationship('destinationAccount', 'name')
                    ->required()
                    ->different('source_account_id')
                    ->preload(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix('EUR'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
