<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Pages;

use App\Enums\ModerationState;
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
        $this->translationPayload = $this->extractTranslationPayload($data);

        $data['moderation_state'] = $data['moderation_state'] ?? ModerationState::Draft;
        $data['is_visible'] = false;
        $data['submitted_for_review_at'] = null;
        $data['approved_at'] = null;
        $data['approved_by_id'] = null;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        $this->persistTranslation($record);

        return $record;
    }

    private function extractTranslationPayload(array &$data): array
    {
        $locale = $this->getActiveLocale();
        $translationData = data_get($data, "translations.{$locale}", []);

        unset($data['translations']);

        if ($translationData === []) {
            return [];
        }

        return [
            'title'           => $translationData['title'] ?? null,
            'slug'            => $translationData['slug'] ?? null,
            'summary'         => $translationData['summary'] ?? null,
            'content'         => $translationData['content'] ?? null,
            'seo_title'       => $translationData['seo_title'] ?? null,
            'seo_description' => $translationData['seo_description'] ?? null,
        ];
    }

    private function persistTranslation(Model $record): void
    {
        if ($this->translationPayload === []) {
            return;
        }

        $locale = $this->getActiveLocale();

        $record->translations()->updateOrCreate(
            ['locale' => $locale],
            array_merge($this->translationPayload, ['locale' => $locale])
        );
    }

    private function getActiveLocale(): string
    {
        return app()->getLocale();
    }
}
