<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingOptionResource\Pages;

use App\Filament\Resources\ShippingOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditShippingOption extends EditRecord
{
    protected static string $resource = ShippingOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Persist the matrix state as a consistent boolean map on update.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $state = $this->form->getRawState();

        $data['shipping_matrix'] = ShippingOptionResource::normalizeMatrixState($state['shipping_matrix'] ?? []);
        // Maintain optional associations when only the matrix changes to avoid validation noise.
        $data['city_id'] = isset($state['city_id']) && $state['city_id'] !== ''
            ? (int) $state['city_id']
            : null;
        $data['service_type'] = ShippingOptionResource::resolveServiceType($state['service_type'] ?? $this->record->service_type);
        $data['zone_id'] = ShippingOptionResource::resolveZoneId($data['zone_id'] ?? ($state['zone_id'] ?? $this->record->zone_id));

        return $data;
    }
}
