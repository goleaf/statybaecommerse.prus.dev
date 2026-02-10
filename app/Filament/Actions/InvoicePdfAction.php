<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Contracts\DocumentServiceContract;
use App\Enums\DocumentTemplateType;
use App\Models\DocumentTemplate;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Throwable;

final class InvoicePdfAction
{
    public static function make(): Action
    {
        return Action::make('generate_invoice_pdf')
            ->label(__('Generate Invoice PDF'))
            ->icon('heroicon-m-document-text')
            ->color('success')
            ->action(function (Model $record, DocumentServiceContract $documentService) {
                try {
                    $template = DocumentTemplate::query()
                        ->active()
                        ->where('type', DocumentTemplateType::Invoice->value)
                        ->orderByDesc('updated_at')
                        ->first();

                    if (! $template) {
                        Notification::make()
                            ->title(__('admin.notifications.document_generation_failed'))
                            ->body(__('Invoice template not found.'))
                            ->danger()
                            ->send();

                        return null;
                    }

                    $document = $documentService->generateDocument(
                        template: $template,
                        relatedModel: $record,
                        title: sprintf('%s_%s_%s', $template->name, $record->getAttribute('number') ?? $record->getKey(), now()->format('Y-m-d_H-i'))
                    );

                    $downloadUrl = $documentService->generatePdf($document);

                    Notification::make()
                        ->title(__('admin.notifications.document_generated'))
                        ->body(__('admin.notifications.document_generated_successfully'))
                        ->success()
                        ->send();

                    return redirect()->away($downloadUrl);
                } catch (Throwable $e) {
                    Notification::make()
                        ->title(__('admin.notifications.document_generation_failed'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    throw $e;
                }
            });
    }
}
