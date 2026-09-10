<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'quantity' => (int) $this->quantity,
            'image' => $this->image,
            'status' => $this->status,
            'avg_rating' => (float) ($this->avg_rating ?? 0),
            'total_reviews' => (int) ($this->total_reviews ?? 0),
        ];
    }
}
