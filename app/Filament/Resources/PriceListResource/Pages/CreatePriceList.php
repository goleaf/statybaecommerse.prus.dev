<?php declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\Pages;

use App\Filament\Resources\PriceListResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePriceList extends CreateRecord
{
    protected static string $resource = PriceListResource::class;

}
