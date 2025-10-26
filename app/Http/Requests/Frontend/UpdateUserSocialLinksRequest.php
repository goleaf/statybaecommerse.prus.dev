<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use App\Rules\UrlRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserSocialLinksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'social_links'            => ['nullable', 'array'],
            'social_links.*.platform' => ['required', 'string', 'in:facebook,twitter,instagram,linkedin,youtube,tiktok,github,website'],
            'social_links.*.url'      => ['required', new UrlRule],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
