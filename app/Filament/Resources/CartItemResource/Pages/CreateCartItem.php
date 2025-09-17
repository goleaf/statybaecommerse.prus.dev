<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartItemResource\Pages;

use App\Filament\Resources\CartItemResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCartItem extends CreateRecord
{
    protected static string $resource = CartItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['quantity'] = $data['quantity'] ?? 1;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_saved_for_later'] = $data['is_saved_for_later'] ?? false;
        
        return $data;
    }
}

