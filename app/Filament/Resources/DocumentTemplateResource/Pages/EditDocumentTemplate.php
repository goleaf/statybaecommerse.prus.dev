<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTemplateResource\Pages;

use App\Filament\Resources\DocumentTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentTemplate extends EditRecord
{
    protected static string $resource = DocumentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label(__('admin.document_templates.actions.preview'))
                ->icon('heroicon-o-eye')
                ->url(fn (): string => route('admin.document-templates.preview', $this->record))
                ->openUrlInNewTab(),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
