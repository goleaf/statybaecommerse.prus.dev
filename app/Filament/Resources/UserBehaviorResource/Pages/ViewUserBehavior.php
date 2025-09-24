<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserBehaviorResource\Pages;

use App\Filament\Resources\UserBehaviorResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewUserBehavior extends ViewRecord
{
    protected static string $resource = UserBehaviorResource::class;
}
