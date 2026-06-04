<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\TransactionType;
use App\Models\Category;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('account_id')
                    ->label('Account')
                    ->relationship('account', 'name')
                    ->required()
                    ->preload(),
                Select::make('type')
                    ->options(TransactionType::class)
                    ->required()
                    ->live(),
                DatePicker::make('date')
                    ->required()
                    ->default(now()),
                Select::make('category_id')
                    ->label('Category')
                    ->options(function (callable $get): array {
                        $type = $get('type');

                        $parents = Category::roots()
                            ->with('children')
                            ->get();

                        if ($type === TransactionType::Income->value || $type === TransactionType::Income) {
                            $parents = $parents->where('name', 'Income');
                        } elseif ($type === TransactionType::Expense->value || $type === TransactionType::Expense) {
                            $parents = $parents->where('name', '!=', 'Income');
                        }

                        $grouped = [];
                        foreach ($parents as $parent) {
                            $children = $parent->children->pluck('name', 'id')->toArray();
                            if ($children) {
                                $grouped[$parent->name] = $children;
                            }
                        }

                        return $grouped;
                    })
                    ->required()
                    ->searchable(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix('EUR'),
                TextInput::make('description')
                    ->maxLength(255),
            ]);
    }
}
