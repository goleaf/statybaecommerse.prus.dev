<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTemplateResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\DocumentTemplateResource;
use App\Models\DocumentTemplate;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

class ListDocumentTemplates extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = DocumentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export')
                ->label(__('document_templates.actions.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (): BinaryFileResponse => $this->exportTemplates()),
        ];
    }

    private function exportTemplates(): BinaryFileResponse
    {
        $filename = sprintf('document-templates-%s.json', now()->format('Y-m-d_H-i-s'));
        $path = "exports/{$filename}";

        $disk = Storage::disk('local');
        $disk->put($path, DocumentTemplate::query()->orderBy('name')->get()->toJson(JSON_PRETTY_PRINT));

        return response()->download($disk->path($path))->deleteFileAfterSend(true);
    }
}
