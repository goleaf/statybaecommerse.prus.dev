<?php declare(strict_types=1);

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
        // Auto-calculate total price if not set
        if (!isset($data['total_price']) && isset($data['quantity']) && isset($data['unit_price'])) {
            $data['total_price'] = $data['quantity'] * $data['unit_price'];
        }

        // Set session_id if not provided
        if (!isset($data['session_id'])) {
            $data['session_id'] = session()->getId();
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('admin.cart_items.notifications.created');
    }
}
