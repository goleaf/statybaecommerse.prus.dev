<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantPricingRuleResource\Pages;

use App\Filament\Resources\VariantPricingRuleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

final class CreateVariantPricingRule extends CreateRecord
{
    protected static string $resource = VariantPricingRuleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('variant_pricing_rules.messages.created_successfully'))
            ->body(__('variant_pricing_rules.messages.created_successfully_description'));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default priority if not provided
        if (!isset($data['priority'])) {
            $data['priority'] = 0;
        }

        // Ensure conditions and pricing_modifiers are arrays
        $data['conditions'] = $data['conditions'] ?? [];
        $data['pricing_modifiers'] = $data['pricing_modifiers'] ?? [];

        return $data;
    }
}
