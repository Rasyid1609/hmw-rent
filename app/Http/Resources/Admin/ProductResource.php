<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'prod_code' => $this->prod_code,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'release_year' => $this->release_year,
            'status' => $this->status,
            'cover' => $this->cover ? Storage::url($this->cover) : null,
            'price' => number_format($this->price, 0, ',', '.'),
            'created_at' => $this->created_at->format('d M Y'),
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'brand' => [
                'id' => $this->brand?->id,
                'name' => $this->brand?->name,
            ],
            'stock' => [
                'total' => $this->stock?->total,
                'available' => $this->stock?->available,
                'borrow' => $this->stock?->borrow,
                'lost' => $this->stock?->lost,
                'damaged' => $this->stock?->damaged,
            ],
        ];
    }
}
