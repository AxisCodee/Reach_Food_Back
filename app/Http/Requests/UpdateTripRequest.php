<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
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
            'address_id' => ['exists:addresses,id'],
            'day_id' => ['exists:days,id'],
            'start_time' => ['date_format:H:i'],
            'salesman_id' => ['exists:users,id'],
            'orders' => ['array'],
            'orders.*.order_id' => ['exists:orders,id'],
            'orders.*.delivery_time' => ['date_format:H:i'],
        ];
    }
}
