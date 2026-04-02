<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('icon')
                    ->maxLength(10),
                ColorPicker::make('color'),
                Select::make('parent_id')
                    ->label('Parent category')
                    ->options(Category::roots()->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
            ]);
    }
}
