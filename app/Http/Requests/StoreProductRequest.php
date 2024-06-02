<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'name' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'description' => 'nullable|string',
            'stock_quantity' => 'nullable|numeric',
            'amount_unit' => 'required|in:kg,piece',
            'wholesale_price' => 'required|numeric',
            'retail_price' => 'required|numeric',
            'image' => 'nullable|file'
        ];
    }
}
