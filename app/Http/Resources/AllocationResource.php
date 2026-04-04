<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllocationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source_account_id' => $this->source_account_id,
            'destination_account_id' => $this->destination_account_id,
            'amount' => (float) $this->amount,
            'is_active' => $this->is_active,
            'source_account' => new AccountResource($this->whenLoaded('sourceAccount')),
            'destination_account' => new AccountResource($this->whenLoaded('destinationAccount')),
        ];
    }
}
