<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'min:3',
                'max:255',
                'string',
            ],
            'description' => [
                'required',
                'min:3',
                'max:255',
                'string'
            ],
            'cover' => [
                'nullable',
                'mimes:png,jpg,jpeg,webp',
                'max:4000',
            ],
            'price_prod' => [
                'required',
                'numeric',
                'min:0'
            ],
            'price' => [
                'required',
                'numeric',
                'min:0'
            ],
            'category_id' => [
                'required',
                'exists:categories,id'
            ],
            'brand_id' => [
                'required',
                'exists:brands,id'
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'Barang',
            'description' => 'Deskripsi',
            'cover' => 'Cover',
            'category_id' => 'Kategori',
            'brand_id' => 'Brands',
        ];
    }
}
