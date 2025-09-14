<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductHistoryResource\Pages;

use App\Filament\Resources\ProductHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewProductHistory extends ViewRecord
{
    protected static string $resource = ProductHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('view_product')
                ->label('View Product')
                ->icon('heroicon-o-eye')
                ->url(fn () => route('admin.products.edit', $this->record->product_id))
                ->openUrlInNewTab()
                ->color('info'),
        ];
    }
}
