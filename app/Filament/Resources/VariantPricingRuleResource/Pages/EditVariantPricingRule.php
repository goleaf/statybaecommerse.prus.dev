<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantPricingRuleResource\Pages;

use App\Filament\Resources\VariantPricingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

final class EditVariantPricingRule extends EditRecord
{
    protected static string $resource = VariantPricingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('variant_pricing_rules.messages.updated_successfully'))
            ->body(__('variant_pricing_rules.messages.updated_successfully_description'));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure conditions and pricing_modifiers are arrays
        $data['conditions'] = $data['conditions'] ?? [];
        $data['pricing_modifiers'] = $data['pricing_modifiers'] ?? [];

        return $data;
    }
}
