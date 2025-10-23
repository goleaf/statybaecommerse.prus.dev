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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

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
                    ->relationship('template', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('format')
                    ->label(__('admin.fields.format'))
                    ->options([
                        'html' => __('HTML'),
                        'pdf'  => __('PDF'),
                    ])
                    ->default('pdf')
                    ->required(),

                TextInput::make('title')
                    ->label(__('admin.fields.title'))
                    ->default(fn (DiscountCode $record) => "Discount Code - {$record->code}")
                    ->required(),
            ])
            ->action(function (DiscountCode $record, array $data, DocumentServiceContract $documentService): RedirectResponse|Response {
                try {
                    $template = DocumentTemplate::query()->findOrFail($data['template_id']);

                    $document = $documentService->generateDocument(
                        template: $template,
                        relatedModel: $record,
                        variables: self::buildVariables($record),
                        title: $data['title']
                    );

                    Notification::make()
                        ->title(__('admin.notifications.document_generated'))
                        ->body(__('admin.notifications.document_generated_successfully'))
                        ->success()
                        ->send();

                    if ($data['format'] === 'pdf') {
                        $downloadUrl = $documentService->generatePdf($document);

                        return redirect()->away($downloadUrl);
                    }

                    return response($document->content ?? '', 200, [
                        'Content-Type' => 'text/html',
                    ]);
                } catch (\Throwable $e) {
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
            'DISCOUNT_CODE' => $record->code,
            'DISCOUNT_NAME' => $record->discount?->name ?? '',
            'DISCOUNT_DESCRIPTION' => $record->description,
            'DISCOUNT_VALUE' => $record->discount?->value ?? '',
            'DISCOUNT_TYPE' => $record->discount?->type ?? '',
            'USAGE_LIMIT' => $record->usage_limit ?? 'Unlimited',
            'USAGE_COUNT' => $record->usage_count,
            'REMAINING_USES' => $record->remaining_uses ?? 'Unlimited',
            'STARTS_AT' => $record->starts_at?->format('d/m/Y H:i') ?? 'Immediately',
            'EXPIRES_AT' => $record->expires_at?->format('d/m/Y H:i') ?? 'Never',
            'STATUS' => $record->status,
            'IS_ACTIVE' => $record->is_active ? 'Yes' : 'No',
            'CREATED_AT' => $record->created_at?->format('d/m/Y H:i') ?? '',
            'UPDATED_AT' => $record->updated_at?->format('d/m/Y H:i') ?? '',
        ];
    }
}
