<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionReturnProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'return_product_code' => $this->return_product_code,
            'product' => $this->whenLoaded('product', [
                'id' => $this->product?->id,
                'title' => $this->product?->title,
            ]),
            'user' => $this->whenLoaded('user', [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ])
        ];
    }
}
