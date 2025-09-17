<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Pages;

use App\Filament\Resources\DiscountConditionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateDiscountCondition extends CreateRecord
{
    protected static string $resource = DiscountConditionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['valid_from'] = $data['valid_from'] ?? now();
        
        return $data;
    }
}

