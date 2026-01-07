<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsTags\Pages;

use App\Filament\Resources\NewsTags\NewsTagResource;
use App\Filament\Resources\NewsTags\Pages\Concerns\HandlesNewsTagTranslations;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsTag extends CreateRecord
{
    use HandlesNewsTagTranslations;

    protected static string $resource = NewsTagResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->prepareNewsTagFormData($data);
    }
}
