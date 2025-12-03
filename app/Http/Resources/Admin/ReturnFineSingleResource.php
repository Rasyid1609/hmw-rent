<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturnFineSingleResource extends JsonResource
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
            'return_date' => Carbon::parse($this->return_date)->format('d M Y'),
            'dayslate' => $this->dayslate,
            'product' => $this->whenLoaded('product', [
                'id' => $this->product?->id,
                'title' => $this->product?->title,
            ]),
            'fine' => $this->whenLoaded('fine', [
                'id' => $this->fine?->id,
                'late_fee' => $this->fine?->late_fee,
                'other_fee' => $this->fine?->other_fee,
                'total_fee' => $this->fine?->total_fee,
                'payment_status' => $this->fine?->payment_status,
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
            'return_product_check' => $this->whenLoaded('returnProductCheck', [
                'condition' => $this->returnProductCheck?->condition,
                'notes' => $this->returnProductCheck?->notes,
            ]),
         ];
    }
}
