<?php declare(strict_types=1);

namespace App\Filament\Resources\AttributeValueResource\Pages;

use App\Filament\Resources\AttributeValueResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditAttributeValue extends EditRecord
{
    protected static string $resource = AttributeValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure slug is generated if not provided
        if (empty($data['slug']) && !empty($data['value'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['value']);
        }

        return $data;
    }
}
