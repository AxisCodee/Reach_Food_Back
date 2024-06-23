<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
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
            'name' => 'string|max:255',
            'user_name' => 'string|max:255',
            'password' => 'string',
            'customer_type' => 'in:shop,center',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address_id' => 'exists:addresses,id',
            'location' => 'string|max:255',
            'phone_number' => 'array',
            'phone_number.*' => 'string|max:255',


            // salesman
            'branches' => 'array',
            'branches.*branch_id' => 'exists:branches,id',
            'branches.*salesManger_id' => 'exists:users,id',
//            'trips' => 'array',
//            'trips.*address_id' => ['required', 'exists:addresses,id'],
//            'trips.*day_id' => ['required', 'exists:days,id'],
//            'trips.*start_time' => ['required', 'date_format:H:i'],

            'permissions' => 'array',
            'permissions.*permission_id' => 'exists:permissions,id',
            'permissions.*status' => 'in:true,false',
            // sales manager
            'branch_id' => 'exists:branches,id',
            'salesmen' => 'array',
            'salesmen.*' => 'exists:users,id'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $transformedErrors = [];
        foreach ($errors->all() as $errorMessage) {
            $transformedErrors[] = $errorMessage;
        }
        throw new HttpResponseException(response()->json([
            'message' => 'Validation Error',
            'errors' => $transformedErrors,
        ], 422));
    }
}
