<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceResource\Pages;

use App\Filament\Resources\PriceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePrice extends CreateRecord
{
    protected static string $resource = PriceResource::class;
}
