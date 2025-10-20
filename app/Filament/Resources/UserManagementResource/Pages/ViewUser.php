<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\Pages;

use App\Filament\Resources\UserManagementResource;
use App\Filament\Resources\UserResource\Pages\ViewUser as BaseViewUser;

final class ViewUser extends BaseViewUser
{
    protected static string $resource = UserManagementResource::class;
}
