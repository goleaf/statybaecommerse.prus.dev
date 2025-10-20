<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTemplateResource\Pages;

use App\Filament\Resources\DocumentTemplateResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Http\RedirectResponse;

class EditDocumentTemplate extends EditRecord
{
    protected static string $resource = DocumentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\Action::make('duplicate')
                ->label(__('document_templates.actions.duplicate'))
                ->icon('heroicon-o-document-duplicate')
                ->requiresConfirmation()
                ->action(function (): RedirectResponse {
                    $duplicate = DocumentTemplateResource::duplicateTemplate($this->record);

                    Notification::make()
                        ->success()
                        ->title(__('document_templates.notifications.duplicated'))
                        ->send();

                    return $this->redirect(DocumentTemplateResource::getUrl('edit', ['record' => $duplicate]));
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
