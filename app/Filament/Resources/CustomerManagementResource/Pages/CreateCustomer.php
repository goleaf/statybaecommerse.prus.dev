<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\Pages;

use App\Filament\Resources\CustomerManagementResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable as TranslatableCreateRecord;

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
        // Convert the human-friendly preferred language selector into the stored locale column.
        $data['preferred_locale'] = $data['preferred_language'] ?? $data['preferred_locale'] ?? null;
        unset($data['preferred_language']);

        // Ensure boolean preference toggles are explicitly cast to booleans for mass assignment.
        if (array_key_exists('newsletter_subscription', $data)) {
            $data['newsletter_subscription'] = (bool) $data['newsletter_subscription'];
        }

        if (array_key_exists('sms_notifications', $data)) {
            $data['sms_notifications'] = (bool) $data['sms_notifications'];
        }

        return $data;
    }
}
