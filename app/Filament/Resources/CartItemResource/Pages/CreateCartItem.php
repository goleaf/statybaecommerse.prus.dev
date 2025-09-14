<?php

declare (strict_types=1);
namespace App\Filament\Resources\CartItemResource\Pages;

use App\Filament\Resources\CartItemResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateCartItem
 * 
 * Filament v4 resource for CreateCartItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreateCartItem extends CreateRecord
{
    protected static string $resource = CartItemResource::class;
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    /**
     * Handle mutateFormDataBeforeCreate functionality with proper error handling.
     * @param array $data
     * @return array
     */
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
    /**
     * Handle getCreatedNotificationTitle functionality with proper error handling.
     * @return string|null
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('admin.cart_items.notifications.created');
    }
}