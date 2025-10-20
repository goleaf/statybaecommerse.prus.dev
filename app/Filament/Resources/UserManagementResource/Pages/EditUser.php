<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\Pages;

use App\Filament\Resources\UserManagementResource;
use App\Filament\Resources\UserResource\Pages\EditUser as BaseEditUser;

final class EditUser extends BaseEditUser
{
    protected static string $resource = UserManagementResource::class;
}
