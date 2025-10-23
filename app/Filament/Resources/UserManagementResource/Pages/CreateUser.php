<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\Pages;

use App\Filament\Resources\UserManagementResource;
use App\Filament\Resources\UserResource\Pages\CreateUser as BaseCreateUser;

final class CreateUser extends BaseCreateUser
{
    protected static string $resource = UserManagementResource::class;
}
