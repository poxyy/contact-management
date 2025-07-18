<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRegisterRequest extends FormRequest
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
            'username' => 'required|string|min:2|max:50',
            'password' => 'required|string|min:3|max:100',
            'name' => 'required|string|min:2|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username must not be blank',
            'username.min' => 'Username must be at least 2 characters',

            'password.required' => 'Password must not be blank',
            'password.min' => 'Password must be at least 3 characters',

            'name.required' => 'Name must not be blank',
            'name.min' => 'Name must be at least 2 characters',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 400));
    }
}
