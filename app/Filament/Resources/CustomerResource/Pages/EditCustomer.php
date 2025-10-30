<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Expose the form schema key explicitly so Filament's test macros resolve the page form.
     */
    public function getDefaultTestingSchemaName(): ?string
    {
        // Defer to the parent calculation while ensuring Livewire never receives a null identifier.
        return parent::getDefaultTestingSchemaName() ?? 'form';
    }
}
