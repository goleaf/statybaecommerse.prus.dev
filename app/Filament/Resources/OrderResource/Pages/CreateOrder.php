<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['number'])) {
            $data['number'] = 'ORD-' . strtoupper(uniqid());
        }

        return $data;
    }

    public function getTitle(): string
    {
        return __('admin.orders.create');
    }

    public function getSubheading(): ?string
    {
        return __('admin.orders.description');
    }
}
