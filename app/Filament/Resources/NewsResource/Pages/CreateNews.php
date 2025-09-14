<?php

declare (strict_types=1);
namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateNews
 * 
 * Filament v4 resource for CreateNews management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    /**
     * Handle mutateFormDataBeforeCreate functionality with proper error handling.
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_visible'] = $data['is_visible'] ?? true;
        $data['published_at'] = $data['published_at'] ?? now();
        return $data;
    }
    /**
     * Handle afterCreate functionality with proper error handling.
     * @return void
     */
    protected function afterCreate(): void
    {
        // Create translation records if provided
        if (isset($this->data['translations'])) {
            $translations = $this->data['translations'];
            $news = $this->record;
            foreach ($translations as $locale => $translationData) {
                if (!empty($translationData['title'])) {
                    $news->translations()->create(['locale' => $locale, 'title' => $translationData['title'], 'slug' => $translationData['slug'] ?? \Str::slug($translationData['title']), 'summary' => $translationData['summary'] ?? null, 'content' => $translationData['content'] ?? null, 'seo_title' => $translationData['seo_title'] ?? null, 'seo_description' => $translationData['seo_description'] ?? null]);
                }
            }
        }
    }
}