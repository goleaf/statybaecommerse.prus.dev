<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\Pages;

use App\Filament\Resources\UserManagementResource;
use App\Filament\Resources\UserResource\Pages\ListUsers as BaseListUsers;

final class ListUsers extends BaseListUsers
{
    protected static string $resource = UserManagementResource::class;
}
