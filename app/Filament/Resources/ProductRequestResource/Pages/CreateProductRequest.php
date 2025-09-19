<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductRequestResource\Pages;

use App\Filament\Resources\ProductRequestResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateProductRequest extends CreateRecord
{
    protected static string $resource = ProductRequestResource::class;
}

