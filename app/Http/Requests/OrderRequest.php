<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            'customer_id' => 'exists:users,id',
            'status'=> 'in:pending, accepted, cancelled, delivered',
            'order_date'=> 'date',
            'delivery_date'=> 'date',
            'delivery_time'=> '',
            'total_price'=> '',
            'category_id'=> '',

        ];
    }
}
