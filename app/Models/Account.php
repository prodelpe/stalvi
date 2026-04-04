<?php

namespace App\Models;

use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'icon', 'color', 'initial_balance'])]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
        ];
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany<Allocation, $this>
     */
    public function outgoingAllocations(): HasMany
    {
        return $this->hasMany(Allocation::class, 'source_account_id');
    }

    /**
     * @return HasMany<Allocation, $this>
     */
    public function incomingAllocations(): HasMany
    {
        return $this->hasMany(Allocation::class, 'destination_account_id');
    }

    /**
     * @return Attribute<float, never>
     */
    protected function balance(): Attribute
    {
        return Attribute::get(function (): float {
            $net = (float) $this->transactions()
                ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as net")
                ->value('net');

            return round((float) $this->initial_balance + $net, 2);
        });
    }
}
