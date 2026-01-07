<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumValueResource\Pages;

use App\Filament\Resources\EnumValueResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEnumValue extends CreateRecord
{
    protected static string $resource = EnumValueResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
