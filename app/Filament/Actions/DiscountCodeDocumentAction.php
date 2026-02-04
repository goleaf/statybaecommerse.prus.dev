<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Contracts\DocumentServiceContract;
use App\Models\DiscountCode;
use App\Models\DocumentTemplate;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Throwable;

final class DiscountCodeDocumentAction
{
    public static function make(): Action
    {
        return Action::make('generate_document')
            ->label(__('admin.actions.generate_document'))
            ->icon('heroicon-m-document-text')
            ->color('info')
            ->form([
                Select::make('template_id')
                    ->label(__('admin.fields.template'))
                    ->options(static function (): array {
                        // Mirror the generic document action and limit choices to active templates only.
                        return DocumentTemplate::query()
                            ->active()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('format')
                    ->label(__('admin.fields.format'))
                    ->options([
                        'html' => 'HTML',
                        'pdf'  => 'PDF',
                    ])
                    ->default('pdf')
                    ->required(),
                TextInput::make('title')
                    ->label(__('admin.fields.title'))
                    ->placeholder(__('admin.placeholders.title')),
            ])
            ->action(function (DiscountCode $record, array $data, DocumentServiceContract $documentService) {
                try {
                    // Double-check the template is active when resolving the selected option from the request payload.
                    $template = DocumentTemplate::query()
                        ->active()
                        ->findOrFail($data['template_id']);

                    $title = $data['title'] ?? sprintf(
                        '%s_%s_%s',
                        $template->name,
                        $record->code,
                        now()->format('Y-m-d_H-i')
                    );

                    $document = $documentService->generateDocument(
                        template: $template,
                        relatedModel: $record,
                        variables: self::buildVariables($record),
                        title: (string) $title
                    );

                    Notification::make()
                        ->title(__('admin.notifications.document_generated'))
                        ->body(__('admin.notifications.document_generated_successfully'))
                        ->success()
                        ->send();

                    if ($data['format'] === 'html') {
                        return response($document->content)->header('Content-Type', 'text/html');
                    }

                    $downloadUrl = $documentService->generatePdf($document);

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

    private static function buildVariables(DiscountCode $record): array
    {
        $record->loadMissing('discount');

        return [
            'DISCOUNT_CODE'        => $record->code,
            'DISCOUNT_NAME'        => $record->discount?->name ?? '',
            'DISCOUNT_DESCRIPTION' => $record->description,
            'DISCOUNT_VALUE'       => $record->discount?->value ?? '',
            'DISCOUNT_TYPE'        => $record->discount?->type ?? '',
            'USAGE_LIMIT'          => $record->usage_limit ?? 'Unlimited',
            'USAGE_COUNT'          => $record->usage_count,
            'REMAINING_USES'       => $record->remaining_uses ?? 'Unlimited',
            'STARTS_AT'            => $record->starts_at?->format('d/m/Y H:i') ?? 'Immediately',
            'EXPIRES_AT'           => $record->expires_at?->format('d/m/Y H:i') ?? 'Never',
            'STATUS'               => $record->status,
            'IS_ACTIVE'            => $record->is_active ? 'Yes' : 'No',
            'CREATED_AT'           => $record->created_at?->format('d/m/Y H:i') ?? '',
            'UPDATED_AT'           => $record->updated_at?->format('d/m/Y H:i') ?? '',
        ];
    }
}
