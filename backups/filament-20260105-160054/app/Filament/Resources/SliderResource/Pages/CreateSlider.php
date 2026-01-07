<?php

declare(strict_types=1);

namespace App\Filament\Resources\SliderResource\Pages;

use App\Filament\Resources\SliderResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSlider extends CreateRecord
{
    protected static string $resource = SliderResource::class;
}
