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
     * Pre-populate the virtual form fields before rendering the edit screen.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['preferred_language'] = $data['preferred_locale'] ?? $data['preferred_language'] ?? null;

        return $data;
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
        $data['preferred_locale'] = $data['preferred_language'] ?? $data['preferred_locale'] ?? null;
        unset($data['preferred_language']);

        if (array_key_exists('newsletter_subscription', $data)) {
            $data['newsletter_subscription'] = (bool) $data['newsletter_subscription'];
        }

        if (array_key_exists('sms_notifications', $data)) {
            $data['sms_notifications'] = (bool) $data['sms_notifications'];
        }

        return $data;
    }
}
