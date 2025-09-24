<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductHistoryResource\Pages;

use App\Filament\Resources\ProductHistoryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateProductHistory extends CreateRecord
{
    protected static string $resource = ProductHistoryResource::class;
}
