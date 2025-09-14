<?php

declare (strict_types=1);
namespace App\Filament\Resources\ProductHistoryResource\Pages;

use App\Filament\Resources\ProductHistoryResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateProductHistory
 * 
 * Filament v4 resource for CreateProductHistory management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreateProductHistory extends CreateRecord
{
    protected static string $resource = ProductHistoryResource::class;
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
        $data['ip_address'] = request()->ip();
        $data['user_agent'] = request()->userAgent();
        $data['user_id'] = auth()->id();
        return $data;
    }
}