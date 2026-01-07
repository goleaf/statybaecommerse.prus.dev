<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\Pages;

use App\Filament\Resources\CustomerGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable as SpatieTranslatableEditRecord;

final class EditCustomerGroup extends EditRecord
{
    use SpatieTranslatableEditRecord;  // Synchronize translated attributes while editing records.

    protected static string $resource = CustomerGroupResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CustomerGroupResource::mutateLocalizedAttributes($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data = CustomerGroupResource::mutateLocalizedAttributes($data);

        return parent::handleRecordUpdate($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),  // Surface locale switching beside the edit actions.
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
