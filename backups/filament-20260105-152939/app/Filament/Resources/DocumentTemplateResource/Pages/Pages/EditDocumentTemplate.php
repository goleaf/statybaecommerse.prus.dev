<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTemplateResource\Pages;

use App\Filament\Resources\DocumentTemplateResource;
use App\Models\DocumentTemplate;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDocumentTemplate extends EditRecord
{
    protected static string $resource = DocumentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, DocumentTemplate $record): void {
                    if (! $record->documents()->exists()) {
                        return;
                    }

                    Notification::make()
                        ->title(__('document_templates.notifications.delete_has_documents.title'))
                        ->body(__('document_templates.notifications.delete_has_documents.body'))
                        ->warning()
                        ->send();

                    $action->halt();
                }),
        ];
    }
}
