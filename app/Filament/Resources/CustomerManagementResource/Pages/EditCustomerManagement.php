<?php declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\Pages;

use App\Filament\Resources\CustomerManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditCustomerManagement extends EditRecord
{
    protected static string $resource = CustomerManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('Edit Customer');
    }
}
