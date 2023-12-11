<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $computedValue = $this->value . "€";
        return [
            'id' => $this->id,
            'value' => $computedValue,
            'datetime' => $this->datetime,
            'notification_read' => $this->notification_read,
            'payment_reference' => $this->payment_reference,
        ];
    }
}
