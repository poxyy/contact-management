<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class AddressUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'street' => 'nullable|string|min:8|max:200',
            'city' => 'nullable|string|min:5|max:100',
            'province' => 'nullable|string|min:5|max:100',
            'country' => 'required|string|min:5|max:100',
            'postal_code' => 'nullable|string|min:3|max:15',
        ];
    }

    public function messages(): array
    {
        return [
            'street.string' => 'Street must be a string.',
            'street.min' => 'Street must be at least 8 characters.',
            'street.max' => 'Street must not exceed 200 characters.',

            'city.string' => 'City must be a string.',
            'city.min' => 'City must be at least 5 characters.',
            'city.max' => 'City must not exceed 100 characters.',

            'province.string' => 'Province must be a string.',
            'province.min' => 'Province must be at least 5 characters.',
            'province.max' => 'Province must not exceed 100 characters.',

            'country.required' => 'Country is required.',
            'country.string' => 'Country must be a string.',
            'country.min' => 'Country must be at least 5 characters.',
            'country.max' => 'Country must not exceed 100 characters.',

            'postal_code.string' => 'Postal code must be a string.',
            'postal_code.min' => 'Postal code invalid format',
            'postal_code.max' => 'Postal code must not exceed 15 characters.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 400));
    }
}
