<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsImageResource\Pages;

use App\Filament\Resources\NewsImageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsImage extends CreateRecord
{
    protected static string $resource = NewsImageResource::class;
}
