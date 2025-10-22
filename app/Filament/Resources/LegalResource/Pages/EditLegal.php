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
                ->label(__('legal.actions.publish'))
                ->icon('heroicon-o-eye')
                ->color('success')
                ->visible(fn () => ! $this->record->published_at)
                ->action(function (): void {
                    $this->record->publish();
                    Notification::make()
                        ->title(__('legal.notifications.published'))
                        ->success()
                        ->send();
                }),
            Actions\Action::make('unpublish')
                ->label(__('legal.actions.unpublish'))
                ->icon('heroicon-o-eye-slash')
                ->color('warning')
                ->visible(fn () => $this->record->published_at)
                ->action(function (): void {
                    $this->record->unpublish();
                    Notification::make()
                        ->title(__('legal.notifications.unpublished'))
                        ->warning()
                        ->send();
                }),
            Actions\Action::make('duplicate')
                ->label(__('legal.actions.duplicate'))
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function (): void {
                    $newRecord = $this->record->replicate();
                    $newRecord->key = $this->record->key . '-copy';
                    $newRecord->published_at = null;
                    $newRecord->save();

                    // Duplicate translations
                    foreach ($this->record->translations as $translation) {
                        $newTranslation = $translation->replicate();
                        $newTranslation->legal_id = $newRecord->id;
                        $newTranslation->save();
                    }

                    Notification::make()
                        ->title(__('legal.notifications.duplicated'))
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
