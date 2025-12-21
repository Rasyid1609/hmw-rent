<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReturnProductCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ReturnProductRequest extends FormRequest
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
            'condition' => [
                'required',
                new Enum(ReturnProductCondition::class),
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'condition' => 'Kondisi',
        ];
    }
}
