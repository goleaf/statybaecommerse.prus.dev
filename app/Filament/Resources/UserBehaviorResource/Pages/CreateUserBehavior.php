<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserBehaviorResource\Pages;

use App\Filament\Resources\UserBehaviorResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateUserBehavior extends CreateRecord
{
    protected static string $resource = UserBehaviorResource::class;
}
