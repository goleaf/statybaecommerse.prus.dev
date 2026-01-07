<?php

declare(strict_types=1);

namespace App\Data\Users;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CreateUserData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        public string $email,

        #[Required]
        public string $password,

        public ?string $phone = null,
        public ?string $dateOfBirth = null,
        public ?string $preferredLocale = null,
        public ?string $role = null,
        public ?bool $isActive = true,
        public ?bool $emailVerified = false,
    ) {}

    public static function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'        => ['required', 'string', 'min:8'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'dateOfBirth'     => ['nullable', 'date', 'before:today'],
            'preferredLocale' => ['nullable', 'string', 'in:lt,en'],
            'role'            => ['nullable', 'string', 'exists:roles,name'],
            'isActive'        => ['nullable', 'boolean'],
            'emailVerified'   => ['nullable', 'boolean'],
        ];
    }
}
