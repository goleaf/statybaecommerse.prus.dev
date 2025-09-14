<?php

declare (strict_types=1);
namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateOrder
 * 
 * Filament v4 resource for CreateOrder management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
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
        // Generate order number if not provided
        if (empty($data['number'])) {
            $data['number'] = 'ORD-' . strtoupper(uniqid());
        }
        // Set default currency
        if (empty($data['currency'])) {
            $data['currency'] = 'EUR';
        }
        return $data;
    }
}