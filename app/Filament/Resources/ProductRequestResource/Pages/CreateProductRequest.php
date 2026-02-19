<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductRequestResource\Pages;

use App\Filament\Resources\ProductRequestResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateProductRequest extends CreateRecord
{
    protected static string $resource = ProductRequestResource::class;

    /**
     * Guard against DB integrity errors by ensuring required request identity data
     * exists before persisting and auto-filling from the selected user when possible.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = null;
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;

        if ($userId !== null) {
            $user = User::query()->find($userId);
            if ($user !== null) {
                if (blank($data['name'] ?? null)) {
                    $data['name'] = $user->name;
                }
                if (blank($data['email'] ?? null)) {
                    $data['email'] = $user->email;
                }
                if (blank($data['phone'] ?? null)) {
                    $data['phone'] = $user->phone ?? $user->phone_number;
                }
            }
        }

        $errors = [];

        if (blank($data['user_id'] ?? null)) {
            $errors['user_id'] = __('validation.required');
        } elseif ($user === null) {
            $errors['user_id'] = __('validation.exists', ['attribute' => __('messages.user')]);
        }
        if (blank($data['name'] ?? null)) {
            $errors['name'] = __('validation.required');
        }
        if (blank($data['email'] ?? null)) {
            $errors['email'] = __('validation.required');
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }
}
