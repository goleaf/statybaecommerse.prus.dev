<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLegal extends EditRecord
{
    protected static string $resource = LegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-o-eye')
                ->color('success')
                ->visible(fn () => ! $this->record->published_at)
                ->action(function () {
                    $this->record->publish();
                    Notification::make()
                        ->title('Document published successfully')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('unpublish')
                ->label('Unpublish')
                ->icon('heroicon-o-eye-slash')
                ->color('warning')
                ->visible(fn () => $this->record->published_at)
                ->action(function () {
                    $this->record->unpublish();
                    Notification::make()
                        ->title('Document unpublished successfully')
                        ->warning()
                        ->send();
                }),
            Actions\Action::make('duplicate')
                ->label('Duplicate')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function () {
                    $newRecord = $this->record->replicate();
                    $newRecord->key = $this->record->key.'-copy';
                    $newRecord->published_at = null;
                    $newRecord->save();

                    // Duplicate translations
                    foreach ($this->record->translations as $translation) {
                        $newTranslation = $translation->replicate();
                        $newTranslation->legal_id = $newRecord->id;
                        $newTranslation->save();
                    }

                    Notification::make()
                        ->title('Document duplicated successfully')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $newRecord]));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-publish if not set and enabled
        if ($data['is_enabled'] && ! $data['published_at']) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
