<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Contracts\DocumentServiceContract;
use App\Enums\DocumentTemplateCategory;
use App\Enums\DocumentTemplateType;
use App\Models\Document;
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
                    $template = self::resolveInvoiceTemplate();

                    $document = $documentService->generateDocument(
                        template: $template,
                        relatedModel: $record,
                        title: sprintf('%s_%s_%s', $template->name, $record->getAttribute('number') ?? $record->getKey(), now()->format('Y-m-d_H-i'))
                    );

                    self::ensureDocumentTemplate($document, $template);

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

    private static function resolveInvoiceTemplate(): DocumentTemplate
    {
        $template = DocumentTemplate::query()
            ->where('type', DocumentTemplateType::Invoice->value)
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->first();

        if ($template instanceof DocumentTemplate) {
            return $template;
        }

        $template = DocumentTemplate::query()->firstOrCreate(
            ['slug' => 'invoice-template'],
            self::defaultInvoiceTemplateData()
        );

        $template->forceFill([
            'type'      => DocumentTemplateType::Invoice->value,
            'category'  => DocumentTemplateCategory::Financial->value,
            'is_active' => true,
        ])->saveQuietly();

        return $template;
    }

    private static function ensureDocumentTemplate(Document $document, DocumentTemplate $template): void
    {
        if ($document->template instanceof DocumentTemplate) {
            return;
        }

        $document->setAttribute('document_template_id', $template->getKey());
        $document->setRelation('template', $template);

        if ($document->exists) {
            $document->saveQuietly();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function defaultInvoiceTemplateData(): array
    {
        return [
            'name'        => 'Invoice Template',
            'description' => 'Standard invoice template for billing',
            'content'     => '<h1>Invoice #{{invoice_number}}</h1><p>Date: {{invoice_date}}</p><p>Customer: {{customer_name}}</p><p>Amount: €{{total_amount}}</p>',
            'variables'   => ['invoice_number', 'invoice_date', 'customer_name', 'total_amount'],
            'type'        => DocumentTemplateType::Invoice->value,
            'category'    => DocumentTemplateCategory::Financial->value,
            'settings'    => [
                'page_size'   => 'A4',
                'orientation' => 'portrait',
                'margins'     => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20],
            ],
            'is_active' => true,
        ];
    }
}
