<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use App\Rules\UrlRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name'             => ['required', 'string', 'max:255'],
            'first_name'       => ['nullable', 'string', 'max:255'],
            'last_name'        => ['nullable', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'phone_number'     => ['nullable', 'string', 'max:255'],
            'gender'           => ['nullable', 'in:male,female,other'],
            'birth_date'       => ['nullable', 'date'],
            'bio'              => ['nullable', 'string', 'max:1000'],
            'company'          => ['nullable', 'string', 'max:255'],
            'position'         => ['nullable', 'string', 'max:255'],
            'website'          => ['nullable', new UrlRule, 'max:255'],
            'preferred_locale' => ['required', 'in:en,lt'],
            'timezone'         => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
