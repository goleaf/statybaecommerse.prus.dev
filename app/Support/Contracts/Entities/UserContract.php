<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\User;
use function array_filter;
use function trim;

final class UserContract
{
    public static function fromModel(User $user): array
    {
        $user->loadMissing('roles');

        $name = (string) ($user->name ?: trim(($user->first_name ?? '').' '.($user->last_name ?? '')));

        return [
            'id' => (int) $user->getKey(),
            'email' => (string) $user->email,
            'name' => $name !== '' ? $name : (string) $user->email,
            'roles' => $user->roles->pluck('name')->unique()->values()->toArray(),
            'meta' => array_filter([
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'preferred_locale' => $user->preferred_locale,
                'phone' => $user->phone_number,
                'company' => $user->company,
                'position' => $user->position,
                'created_at' => optional($user->created_at)->toISOString(),
                'updated_at' => optional($user->updated_at)->toISOString(),
            ], static fn ($value) => $value !== null && $value !== ''),
        ];
    }
}
