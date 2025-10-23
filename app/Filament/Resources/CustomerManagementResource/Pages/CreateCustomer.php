<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\Pages;

use App\Filament\Resources\CustomerManagementResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable as TranslatableCreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CreateCustomer extends CreateRecord
{
    use TranslatableCreateRecord;

    protected static string $resource = CustomerManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }

    /**
     * Normalise the Livewire form payload before persisting the customer.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->mapFormFieldsToModelAttributes($data);
    }

    /**
     * Map virtual form fields (such as preferred_language) to real columns.
     */
    private function mapFormFieldsToModelAttributes(array $data): array
    {
        $data['preferred_locale'] = $data['preferred_language'] ?? $data['preferred_locale'] ?? null;
        unset($data['preferred_language']);

        $preferences = $data['preferences'] ?? [];

        if (array_key_exists('preferences->preferred_currency', $data)) {
            $preferences['preferred_currency'] = $data['preferences->preferred_currency'];
            unset($data['preferences->preferred_currency']);
        }

        if (isset($preferences['preferred_currency']) && $preferences['preferred_currency'] !== null && $preferences['preferred_currency'] !== '') {
            $data['preferred_currency'] = $preferences['preferred_currency'];
        }

        unset($data['preferences']);

        $notificationPreferencesInput = $data['notification_preferences'] ?? [];

        if (array_key_exists('notification_preferences->newsletter_subscription', $data)) {
            $notificationPreferencesInput['newsletter_subscription'] = $data['notification_preferences->newsletter_subscription'];
            unset($data['notification_preferences->newsletter_subscription']);
        }

        if (array_key_exists('notification_preferences->sms_notifications', $data)) {
            $notificationPreferencesInput['sms_notifications'] = $data['notification_preferences->sms_notifications'];
            unset($data['notification_preferences->sms_notifications']);
        }

        $notificationPreferences = [
            'newsletter_subscription' => (bool) ($notificationPreferencesInput['newsletter_subscription'] ?? false),
            'sms_notifications' => (bool) ($notificationPreferencesInput['sms_notifications'] ?? false),
        ];
        $data['notification_preferences'] = json_encode($notificationPreferences, JSON_THROW_ON_ERROR);
        unset($data['newsletter_subscription'], $data['sms_notifications']);

        unset($data['customerGroups']);

        if (! array_key_exists('password', $data) || blank($data['password'])) {
            $data['password'] = Hash::make(Str::random(32));
        }

        return $data;
    }
}
