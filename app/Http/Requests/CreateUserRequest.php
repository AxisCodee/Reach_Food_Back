<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:users',
            'password' => 'required|string',
            'role' => 'in:super admin,admin,customer,salesman,sales manager',
            'customer_type' => 'in:shop,center',
            // user details
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address_id' => 'required|exists:addresses,id',
            'location' => 'required|string|max:255',
            'phone_number' => 'required|array',
            'phone_number.*' => 'required|string|max:255',


            // salesman
            'salesManager_id' => 'exists:users,id',
            'trips' => 'array',
            'trips.*address_id' => ['required', 'exists:addresses,id'],
            'trips.*day_id' => ['required', 'exists:days,id'],
            'trips.*start_time' => ['required', 'date_format:H:i'],
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',

            'permissions' => 'array',
            'permissions.*permission_id' => 'exists:permissions,id',
            'permissions.*status' => 'in:true,false',
            // sales manager
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
