<?php

declare (strict_types=1);
namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use App\Models\Translations\LegalTranslation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
/**
 * EditLegal
 * 
 * Filament v4 resource for EditLegal management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class EditLegal extends EditRecord
{
    protected static string $resource = LegalResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
    /**
     * Handle mutateFormDataBeforeFill functionality with proper error handling.
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load translations data
        $record = $this->record;
        $translations = $record->translations()->get()->keyBy('locale');
        $data['translations'] = ['lt' => ['title' => $translations->get('lt')?->title ?? '', 'slug' => $translations->get('lt')?->slug ?? '', 'content' => $translations->get('lt')?->content ?? '', 'seo_title' => $translations->get('lt')?->seo_title ?? '', 'seo_description' => $translations->get('lt')?->seo_description ?? ''], 'en' => ['title' => $translations->get('en')?->title ?? '', 'slug' => $translations->get('en')?->slug ?? '', 'content' => $translations->get('en')?->content ?? '', 'seo_title' => $translations->get('en')?->seo_title ?? '', 'seo_description' => $translations->get('en')?->seo_description ?? '']];
        return $data;
    }
    /**
     * Handle mutateFormDataBeforeSave functionality with proper error handling.
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle translations data
        $translations = $data['translations'] ?? [];
        unset($data['translations']);
        return $data;
    }
    /**
     * Handle afterSave functionality with proper error handling.
     * @return void
     */
    protected function afterSave(): void
    {
        $record = $this->record;
        $translations = $this->data['translations'] ?? [];
        // Update or create translations
        foreach ($translations as $locale => $translationData) {
            if (!empty($translationData['title'])) {
                LegalTranslation::updateOrCreate(['legal_id' => $record->id, 'locale' => $locale], ['title' => $translationData['title'], 'slug' => $translationData['slug'], 'content' => $translationData['content'] ?? '', 'seo_title' => $translationData['seo_title'] ?? '', 'seo_description' => $translationData['seo_description'] ?? '']);
            }
        }
        Notification::make()->title(__('admin.legal.updated_successfully'))->success()->send();
    }
}