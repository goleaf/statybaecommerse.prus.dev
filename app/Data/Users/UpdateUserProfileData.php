<?php

declare(strict_types=1);

namespace App\Data\Users;

use Spatie\LaravelData\Data;

class UpdateUserProfileData extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $dateOfBirth = null,
        public ?string $preferredLocale = null,
    ) {}

    public static function rules(): array
    {
        return [
            'name'            => ['nullable', 'string', 'max:255'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'dateOfBirth'     => ['nullable', 'date', 'before:today'],
            'preferredLocale' => ['nullable', 'string', 'in:lt,en'],
        ];
    }
}
