<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProductFrontResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryFrontResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'cover' => $this->cover ? Storage::url($this->cover) : null,
            'products' => ProductFrontResource::collection($this->whenLoaded('products')),
        ];
    }
}
