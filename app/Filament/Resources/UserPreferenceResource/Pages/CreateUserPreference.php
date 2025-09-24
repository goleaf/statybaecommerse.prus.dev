<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserPreferenceResource\Pages;

use App\Filament\Resources\UserPreferenceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateUserPreference extends CreateRecord
{
    protected static string $resource = UserPreferenceResource::class;
}
