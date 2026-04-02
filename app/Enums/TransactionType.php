<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TransactionType: string implements HasColor, HasLabel
{
    case Expense = 'expense';
    case Income = 'income';

    public function getLabel(): string
    {
        return match ($this) {
            self::Expense => 'Expense',
            self::Income => 'Income',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Expense => 'danger',
            self::Income => 'success',
        };
    }
}
