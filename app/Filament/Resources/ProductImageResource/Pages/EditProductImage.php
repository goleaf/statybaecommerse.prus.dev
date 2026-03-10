<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductImageResource\Pages;

use App\Filament\Resources\ProductImageResource;
use App\Models\ProductImage;
use App\Services\ProductImages\ProductImageWriteService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProductImage extends EditRecord
{
    protected static string $resource = ProductImageResource::class;

    /**
     * @param array<string, mixed> $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof ProductImage) {
            return $record;
        }

        return app(ProductImageWriteService::class)->update($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->action(fn (ProductImage $record) => app(ProductImageWriteService::class)->delete($record)),
        ];
    }
}
