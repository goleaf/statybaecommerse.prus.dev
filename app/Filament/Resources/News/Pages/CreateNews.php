<?php

declare(strict_types=1);

namespace App\Filament\Resources\News\Pages;

use App\Enums\ModerationState;
use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Concerns\ManagesNewsTranslationTabs;
use App\Filament\Resources\News\NewsResource;
use App\Models\News;
use Filament\Resources\Pages\CreateRecord;

final class CreateNews extends CreateRecord
{
    use InteractsWithTranslationTabs;
    use ManagesNewsTranslationTabs {
        ManagesNewsTranslationTabs::getTranslatableFields insteadof InteractsWithTranslationTabs;
        ManagesNewsTranslationTabs::mutateMainDataWithDefaultLocale insteadof InteractsWithTranslationTabs;
        ManagesNewsTranslationTabs::syncTranslationRecords insteadof InteractsWithTranslationTabs;
    }

    protected static string $resource = NewsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        [$data, $translations] = $this->extractTranslationsFromForm($data);

        $translations = $this->ensureDefaultLocaleSlug($translations);
        $this->assertUniqueSlugs($translations);

        $this->languageTabsPayload = $translations;

        $data = $this->mutateMainDataWithDefaultLocale($data, $translations);

        $adminId = auth('admin')->id();
        if (is_numeric($adminId)) {
            $data['created_by_id'] = (int) $adminId;
            $data['updated_by_id'] = (int) $adminId;
        }

        return $this->normalizeModerationDates($data);
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        if (! $record instanceof News) {
            return;
        }

        $this->syncTranslationRecords($record, $this->languageTabsPayload);
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeModerationDates(array $data): array
    {
        $state = $data['moderation_state'] ?? null;

        if ($state === ModerationState::Review->value && blank($data['submitted_for_review_at'] ?? null)) {
            $data['submitted_for_review_at'] = now();
        }

        if ($state === ModerationState::Published->value && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
