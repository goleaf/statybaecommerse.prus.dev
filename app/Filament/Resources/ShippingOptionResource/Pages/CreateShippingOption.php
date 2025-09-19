<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingOptionResource\Pages;

use App\Filament\Resources\ShippingOptionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateShippingOption extends CreateRecord
{
    protected static string $resource = ShippingOptionResource::class;
}
