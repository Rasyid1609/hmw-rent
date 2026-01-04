<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturnProductFrontResource extends JsonResource
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
            'status' => $this->status,
            'return_date' => $this->return_date ? Carbon::parse($this->return_date)->format('d M Y') : null,
            'created_at' => $this->created_at->format('d M Y'),
            'product' => $this->whenLoaded('product', [
                'id' => $this->product?->id,
                'title' => $this->product?->title,
                'slug' => $this->product?->slug,
            ]),
            'loan' => $this->whenLoaded('loan', [
                'id' => $this->loan?->id,
                'loan_code' => $this->loan?->loan_code,
                'loan_date' => Carbon::parse($this->loan?->loan_date)->format('d M Y'),
                'due_date' => Carbon::parse($this->loan?->due_date)->format('d M Y'),
            ]),
            'user' => $this->whenLoaded('user', [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ]),
            'fine' => $this->whenLoaded('fine', $this->fine?->total_fee),
            'return_product_check' => $this->whenLoaded('returnProductCheck', $this->returnBookCheck?->condition)
        ];

    }
}
