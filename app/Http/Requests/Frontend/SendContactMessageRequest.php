<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class SendContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255'],
            'subject'      => ['required', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'order_number' => ['nullable', 'string', 'max:100'],
            'message'      => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
