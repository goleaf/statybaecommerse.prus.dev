<?php declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\Pages;

use App\Filament\Resources\CustomerManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListCustomerManagement extends ListRecords
{
    protected static string $resource = CustomerManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('export_customers')
                ->label(__('Export Customers'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // Export logic here
                    $this->notify('success', __('Customers exported successfully'));
                }),
        ];
    }

    public function getTitle(): string
    {
        return __('Customer Management');
    }
}
