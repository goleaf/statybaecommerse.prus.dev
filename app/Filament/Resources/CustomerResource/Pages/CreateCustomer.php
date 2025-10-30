<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    /**
     * Hint the default schema so Livewire form helpers target the resource form during tests.
     */
    public function getDefaultTestingSchemaName(): ?string
    {
        // Fall back to the Filament default but guarantee a non-null value for macro calls.
        return parent::getDefaultTestingSchemaName() ?? 'form';
    }
}
