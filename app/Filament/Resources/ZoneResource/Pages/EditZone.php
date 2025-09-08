<?php declare(strict_types=1);

namespace App\Filament\Resources\ZoneResource\Pages;

use App\Filament\Resources\ZoneResource;
use App\Models\Zone;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditZone extends EditRecord
{
    protected static string $resource = ZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('admin.titles.edit_zone');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure only one default zone exists
        if ($data['is_default'] ?? false) {
            Zone::where('is_default', true)
                ->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }

        return $data;
    }
}
