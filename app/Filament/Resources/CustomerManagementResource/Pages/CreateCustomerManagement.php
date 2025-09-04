<?php declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\Pages;

use App\Filament\Resources\CustomerManagementResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCustomerManagement extends CreateRecord
{
    protected static string $resource = CustomerManagementResource::class;

    public function getTitle(): string
    {
        return __('Create Customer');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_admin'] = false;
        $data['is_active'] = $data['is_active'] ?? true;
        
        return $data;
    }
}
