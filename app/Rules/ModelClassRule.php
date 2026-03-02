<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

final class ModelClassRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if (! class_exists($value) || ! is_subclass_of($value, Model::class)) {
            $fail(__('messages.validation_attribute_must_be_valid_eloquent_model_class', ['attribute' => $attribute]));
        }
    }
}
