<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Pages;

use App\Enums\ModerationState;
use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Concerns\ManagesNewsTranslationTabs;
use App\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateNews extends CreateRecord
{
    use InteractsWithTranslationTabs, ManagesNewsTranslationTabs {
        ManagesNewsTranslationTabs::getTranslatableFields insteadof InteractsWithTranslationTabs;
        ManagesNewsTranslationTabs::mutateMainDataWithDefaultLocale insteadof InteractsWithTranslationTabs;
        ManagesNewsTranslationTabs::syncTranslationRecords insteadof InteractsWithTranslationTabs;
    }

    protected static string $resource = NewsResource::class;

    /**
     * @var array<string, mixed>
     */
    private array $translationPayload = [];

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        [$data, $translations] = $this->extractTranslationsFromForm($data);
        $this->languageTabsPayload = $this->ensureDefaultLocaleSlug(
            $this->filterEmptyTranslations($translations)
        );

        $this->assertUniqueSlugs($this->languageTabsPayload);

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
