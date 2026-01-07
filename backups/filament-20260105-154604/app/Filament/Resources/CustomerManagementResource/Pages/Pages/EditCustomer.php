<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\Pages;

use App\Filament\Resources\CustomerManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable as TranslatableEditRecord;

final class EditCustomer extends EditRecord
{
    use TranslatableEditRecord;

    protected static string $resource = CustomerManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Normalise edited data before it is saved back to the database.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->mapFormFieldsToModelAttributes($data);
    }

    /**
     * Reuse the same mapping logic as the create page for consistency.
     */
    private function mapFormFieldsToModelAttributes(array $data): array
    {
        $incomingPreferences = is_array($data['preferences'] ?? null) ? $data['preferences'] : [];

        if (array_key_exists('preferences->preferred_currency', $data)) {
            $incomingPreferences['preferred_currency'] = $data['preferences->preferred_currency'];
            unset($data['preferences->preferred_currency']);
        }

        if (array_key_exists('preferred_currency', $incomingPreferences)) {
            $data['preferred_currency'] = $incomingPreferences['preferred_currency'];
        }

        unset($data['preferences']);

        $existingNotification = (array) ($this->record?->notification_preferences ?? []);
        $incomingNotification = is_array($data['notification_preferences'] ?? null) ? $data['notification_preferences'] : [];

        if (array_key_exists('notification_preferences->newsletter_subscription', $data)) {
            $incomingNotification['newsletter_subscription'] = $data['notification_preferences->newsletter_subscription'];
            unset($data['notification_preferences->newsletter_subscription']);
        }

        if (array_key_exists('notification_preferences->sms_notifications', $data)) {
            $incomingNotification['sms_notifications'] = $data['notification_preferences->sms_notifications'];
            unset($data['notification_preferences->sms_notifications']);
        }

        $notificationPreferences = [
            'newsletter_subscription' => (bool) ($incomingNotification['newsletter_subscription'] ?? ($existingNotification['newsletter_subscription'] ?? false)),
            'sms_notifications'       => (bool) ($incomingNotification['sms_notifications'] ?? ($existingNotification['sms_notifications'] ?? false)),
        ];

        $data['notification_preferences'] = json_encode($notificationPreferences, JSON_THROW_ON_ERROR);

        return $data;
    }
}
