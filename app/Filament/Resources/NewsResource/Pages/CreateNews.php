<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Pages;

use App\Enums\ModerationState;
use App\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateNews extends CreateRecord
{
    use InteractsWithTranslationTabs, ManagesNewsTranslationTabs {
        ManagesNewsTranslationTabs::getTranslatableFields insteadof InteractsWithTranslationTabs;
        ManagesNewsTranslationTabs::mutateMainDataWithDefaultLocale insteadof InteractsWithTranslationTabs;
        ManagesNewsTranslationTabs::syncTranslationRecords insteadof InteractsWithTranslationTabs;
    }

    protected static string $resource = NewsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['moderation_state'] = $data['moderation_state'] ?? ModerationState::Draft;
        $data['is_visible'] = false;
        $data['submitted_for_review_at'] = null;
        $data['approved_at'] = null;
        $data['approved_by_id'] = null;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncTranslationRecords($this->record, $this->languageTabsPayload);

        if (method_exists(CreateRecord::class, 'afterCreate')) {
            parent::afterCreate();
        }
    }
}
