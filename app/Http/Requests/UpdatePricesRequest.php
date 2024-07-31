<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePricesRequest extends FormRequest
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
            'product' => ['required', 'array'],
            'product.*.id' => ['required', 'integer'],
            'product.*.retail_price' => ['required', 'integer'],
            'product.*.wholesale_price' => ['required', 'integer', 'lt:product.*.retail_price'],
        ];
    }
    public function messages()
    {
        return[
            'product.*.wholesale_price.lt' => 'يجب أن يكون سعر الجملة أقل من سعر المفرق'
        ];
    }
}
