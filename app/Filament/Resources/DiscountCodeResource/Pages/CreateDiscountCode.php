<?php declare(strict_types=1);

namespace App\Filament\Resources\DiscountCodeResource\Pages;

use App\Filament\Resources\DiscountCodeResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateDiscountCode extends CreateRecord
{
    protected static string $resource = DiscountCodeResource::class;
}
