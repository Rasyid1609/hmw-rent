<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanFrontSingleResource extends JsonResource
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
            'loan_code' => $this->loan_code,
            'loan_date' => Carbon::parse($this->loan_date)->format('d M Y'),
            'due_date' => Carbon::parse($this->due_date)->format('d M Y'),
            'created_at' => $this->created_at->format('d M Y'),
            'product' => $this->whenLoaded('product', [
                'id' => $this->product?->id,
                'title' => $this->product?->title,
                'slug' => $this->product?->slug,
                'cover' => $this->product?->cover ? Storage::url($this->product?->cover) : null,
                'synopsis' => $this->product?->synopsis,
            ]),
            'return_product' => $this->whenLoaded('returnProduct', [
                'status' => $this->returnProduct?->status,
            ]),
            'user' => $this->whenLoaded('user', [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ])
        ];
    }
}
