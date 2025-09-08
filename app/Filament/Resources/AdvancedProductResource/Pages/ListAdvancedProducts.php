<?php declare(strict_types=1);

namespace App\Filament\Resources\AdvancedProductResource\Pages;

use App\Filament\Resources\AdvancedProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;

final class ListAdvancedProducts extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = AdvancedProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('Create Product'))
                ->icon('heroicon-o-plus'),
                
            Actions\Action::make('import_products')
                ->label(__('Import Products'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label(__('CSV File'))
                        ->acceptedFileTypes(['text/csv', 'application/csv'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    // Import logic here
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => __('Import started in background'),
                    ]);
                }),
                
            Actions\Action::make('export_products')
                ->label(__('Export Products'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    // Export logic here
                    return response()->download(
                        storage_path('app/exports/products-' . now()->format('Y-m-d-H-i-s') . '.csv')
                    );
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdvancedProductResource\Widgets\ProductStatsWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return __('Advanced Product Management');
    }
}
