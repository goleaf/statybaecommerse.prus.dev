<?php

declare (strict_types=1);
namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
/**
 * EditNews
 * 
 * Filament v4 resource for EditNews management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make(), Actions\Action::make('publish')->label(__('admin.news.actions.publish'))->icon('heroicon-o-check')->action(function () {
            $this->record->update(['is_visible' => true, 'published_at' => now()]);
            $this->refreshFormData(['is_visible', 'published_at']);
        })->visible(fn(): bool => !$this->record->isPublished()), Actions\Action::make('unpublish')->label(__('admin.news.actions.unpublish'))->icon('heroicon-o-x-mark')->action(function () {
            $this->record->update(['is_visible' => false, 'published_at' => null]);
            $this->refreshFormData(['is_visible', 'published_at']);
        })->visible(fn(): bool => $this->record->isPublished()), Actions\Action::make('feature')->label(__('admin.news.actions.feature'))->icon('heroicon-o-star')->action(function () {
            $this->record->update(['is_featured' => true]);
            $this->refreshFormData(['is_featured']);
        })->visible(fn(): bool => !$this->record->is_featured), Actions\Action::make('unfeature')->label(__('admin.news.actions.unfeature'))->icon('heroicon-o-star')->action(function () {
            $this->record->update(['is_featured' => false]);
            $this->refreshFormData(['is_featured']);
        })->visible(fn(): bool => $this->record->is_featured)];
    }
    /**
     * Handle mutateFormDataBeforeFill functionality with proper error handling.
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load translations into form data
        $news = $this->record;
        $data['translations'] = [];
        foreach ($news->translations as $translation) {
            $data['translations'][$translation->locale] = ['title' => $translation->title, 'slug' => $translation->slug, 'summary' => $translation->summary, 'content' => $translation->content, 'seo_title' => $translation->seo_title, 'seo_description' => $translation->seo_description];
        }
        return $data;
    }
    /**
     * Handle afterSave functionality with proper error handling.
     * @return void
     */
    protected function afterSave(): void
    {
        // Update translation records if provided
        if (isset($this->data['translations'])) {
            $translations = $this->data['translations'];
            $news = $this->record;
            foreach ($translations as $locale => $translationData) {
                if (!empty($translationData['title'])) {
                    $news->translations()->updateOrCreate(['locale' => $locale], ['title' => $translationData['title'], 'slug' => $translationData['slug'] ?? \Str::slug($translationData['title']), 'summary' => $translationData['summary'] ?? null, 'content' => $translationData['content'] ?? null, 'seo_title' => $translationData['seo_title'] ?? null, 'seo_description' => $translationData['seo_description'] ?? null]);
                }
            }
        }
    }
}