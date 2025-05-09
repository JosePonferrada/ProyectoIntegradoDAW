<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'country_id' => ['nullable', 'exists:countries,country_id'],
            'favorite_driver_id' => ['nullable', 'exists:drivers,driver_id'],
            'favorite_constructor_id' => ['nullable', 'exists:constructors,constructor_id'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
