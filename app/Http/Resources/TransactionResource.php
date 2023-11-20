<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $signedValue = $this->type == 'C' ? "+".$this->value : "-".$this->value;
        $computedValue = $signedValue . "€";
        return [
            'id' => $this->id,
            'type' => $this->type == 'C' ? 'Credit' : 'Debit',
            'value' => $computedValue,
            'datetime' => $this->datetime,
            'payment_type' => $this->payment_type,
            'payment_reference' => $this->payment_reference,
        ];
    }
}
