<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTemplateResource\Pages;

use App\Filament\Resources\DocumentTemplateResource;
use App\Models\DocumentTemplate;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewDocumentTemplate extends ViewRecord
{
    protected static string $resource = DocumentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('preview')
                ->label(__('document_templates.actions.preview'))
                ->icon('heroicon-o-eye')
                ->modalHeading(__('document_templates.actions.preview'))
                ->modalWidth('7xl')
                ->modalSubmitAction(false)
                ->modalContent(fn (DocumentTemplate $record): HtmlString => new HtmlString($record->content)),
        ];
    }
}
