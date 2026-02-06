<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

final class UrlRule implements ValidationRule
{
    public function __construct(
        private readonly array $protocols = ['http', 'https']
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(__('validation.string', ['attribute' => $attribute]));

            return;
        }

        if (empty(trim($value))) {
            $fail(__('validation.required', ['attribute' => $attribute]));

            return;
        }

        if (! Str::isUrl($value, $this->protocols)) {
            $fail(__('validation.url', ['attribute' => $attribute]));
        }
    }
}
