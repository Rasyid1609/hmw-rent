<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductFrontSingleResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'cover' => $this->cover ? Storage::url($this->cover) : null,
            'price' => $this->price,
            'description' => $this->description,
            'release_year' => $this->release_year,
            'created_at' => $this->created_at->format('d M Y'),
            'category' => $this->whenLoaded('category', [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'brands' => $this->whenLoaded('brands', [
                'id' => $this->brands?->id,
                'name' => $this->brands?->name,
            ]),
            'stock' => $this->whenLoaded('stock', [
                'available' => $this->stock?->available,
            ]),
        ];
    }

}
