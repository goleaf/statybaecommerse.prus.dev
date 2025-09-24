<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserPreferenceResource\Pages;

use App\Filament\Resources\UserPreferenceResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewUserPreference extends ViewRecord
{
    protected static string $resource = UserPreferenceResource::class;
}
