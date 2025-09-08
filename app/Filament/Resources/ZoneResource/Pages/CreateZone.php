<?php declare(strict_types=1);

namespace App\Filament\Resources\ZoneResource\Pages;

use App\Filament\Resources\ZoneResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateZone extends CreateRecord
{
    protected static string $resource = ZoneResource::class;

    public function getTitle(): string
    {
        return __('admin.titles.create_zone');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure only one default zone exists
        if ($data['is_default'] ?? false) {
            Zone::where('is_default', true)->update(['is_default' => false]);
        }

        return $data;
    }
}
