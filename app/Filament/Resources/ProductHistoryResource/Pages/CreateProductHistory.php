<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductHistoryResource\Pages;

use App\Filament\Resources\ProductHistoryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateProductHistory extends CreateRecord
{
    protected static string $resource = ProductHistoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['ip_address'] = request()->ip();
        $data['user_agent'] = request()->userAgent();
        $data['user_id'] = auth()->id();

        return $data;
    }
}
