<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsImages\Pages;

use App\Filament\Resources\NewsImages\NewsImageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsImage extends CreateRecord
{
    protected static string $resource = NewsImageResource::class;
}
