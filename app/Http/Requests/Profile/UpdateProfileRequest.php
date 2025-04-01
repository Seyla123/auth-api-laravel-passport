<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|unique:users,email,' . $this->user()->id,
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone' => 'sometimes|string|max:20|nullable',
        ];
    }
}
