<?php declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use App\Services\MultiLanguageTabService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewCampaign extends ViewRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return array_merge(
            $data,
            MultiLanguageTabService::populateFormWithTranslations($this->record, ['name', 'slug', 'description'])
        );
    }
}
