<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'field_id' => $this->field_id,
            'booking_date' => optional($this->booking_date)->format('Y-m-d') ?? $this->booking_date,
            'start_time' => substr((string) $this->start_time, 0, 5),
            'end_time' => substr((string) $this->end_time, 0, 5),
            'total_price' => (float) $this->total_price,
            'status' => $this->status,
            'note' => $this->note,
            'field' => new FieldResource($this->whenLoaded('field')),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'payment' => $this->whenLoaded('payment'),
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}
