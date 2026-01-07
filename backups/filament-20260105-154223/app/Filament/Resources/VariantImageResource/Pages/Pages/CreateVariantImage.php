<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantImageResource\Pages;

use App\Filament\Resources\VariantImageResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateVariantImage extends CreateRecord
{
    protected static string $resource = VariantImageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return VariantImageResource::populateFileMetadata($data);
    }
}
