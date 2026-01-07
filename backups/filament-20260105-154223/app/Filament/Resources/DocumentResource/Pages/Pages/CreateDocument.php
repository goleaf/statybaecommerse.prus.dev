<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;
}
