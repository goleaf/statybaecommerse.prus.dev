<?php

declare (strict_types=1);
namespace App\Filament\Resources\SubscriberResource\Pages;

use App\Filament\Resources\SubscriberResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
/**
 * CreateSubscriber
 * 
 * Filament v4 resource for CreateSubscriber management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreateSubscriber extends CreateRecord
{
    protected static string $resource = SubscriberResource::class;
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    /**
     * Handle getCreatedNotification functionality with proper error handling.
     * @return Notification|null
     */
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()->title('Subscriber created successfully')->success()->body('The subscriber has been added to the mailing list.');
    }
    /**
     * Handle mutateFormDataBeforeCreate functionality with proper error handling.
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['subscribed_at'])) {
            $data['subscribed_at'] = now();
        }
        return $data;
    }
}