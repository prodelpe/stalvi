<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                ColorPicker::make('color'),
                TextInput::make('initial_balance')
                    ->label('Initial balance')
                    ->numeric()
                    ->prefix('EUR')
                    ->default(0),
            ]);
    }
}
