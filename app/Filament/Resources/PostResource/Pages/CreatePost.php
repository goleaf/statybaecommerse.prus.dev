<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

use App\Enums\ModerationState;
use App\Filament\Concerns\InteractsWithJsonTranslationTabs;
use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

final class CreatePost extends CreateRecord
{
    use InteractsWithJsonTranslationTabs;

    protected static string $resource = PostResource::class;

    /**
     * @return array<int, string>
     */
    protected function getTranslatableFields(): array
    {
        return ['title', 'excerpt', 'content', 'meta_title', 'meta_description'];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        [$data, $translations] = $this->extractTranslationsFromForm($data);
        $this->languageTabsPayload = $this->filterEmptyTranslations($translations);

        $data = $this->mutateMainDataWithDefaultLocale($data, $this->languageTabsPayload);
        $data = $this->mergeTranslationsIntoData($data, $this->languageTabsPayload);

        if (blank($data['slug'] ?? null)) {
            $defaultLocale = $this->getDefaultLocale();
            $defaultTitle = $this->languageTabsPayload[$defaultLocale]['title'] ?? null;

            if (filled($defaultTitle)) {
                $data['slug'] = Str::slug($defaultTitle);
            }
        }

        $data['moderation_state'] = $data['moderation_state'] ?? ModerationState::Draft;
        $data['status'] = $data['status'] ?? 'draft';
        $data['submitted_for_review_at'] = null;
        $data['approved_at'] = null;
        $data['approved_by_id'] = null;

        unset($data['images'], $data['gallery']);

        return $data;
    }
}
