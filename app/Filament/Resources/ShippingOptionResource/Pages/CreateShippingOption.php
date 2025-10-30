<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingOptionResource\Pages;

use App\Filament\Resources\ShippingOptionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateShippingOption extends CreateRecord
{
    protected static string $resource = ShippingOptionResource::class;

    /**
     * Ensure the shipping matrix persists as a normalised boolean grid.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $state = $this->form->getRawState();

        $data['shipping_matrix'] = ShippingOptionResource::normalizeMatrixState($state['shipping_matrix'] ?? []);
        // Persist optional associations manually because the form components skip dehydration.
        $data['city_id'] = isset($state['city_id']) && $state['city_id'] !== ''
            ? (int) $state['city_id']
            : null;
        $data['service_type'] = ShippingOptionResource::resolveServiceType($state['service_type'] ?? null);
        $data['zone_id'] = ShippingOptionResource::resolveZoneId($data['zone_id'] ?? ($state['zone_id'] ?? null));

        return $data;
    }
}
