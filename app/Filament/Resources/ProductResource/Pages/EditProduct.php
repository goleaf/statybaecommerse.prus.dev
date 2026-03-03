<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Support\Products\ProductPublicationGuard;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        if ($record instanceof Product) {
            ProductPublicationGuard::ensureEditPublishability($data, $record);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
