<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Contracts\DocumentServiceContract;
use App\Models\DocumentTemplate;
use DateTimeInterface;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Throwable;

final class DocumentAction
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
                        // Only expose active templates in alphabetical order to keep the picker predictable.
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
                        'html' => __('HTML'),
                        'pdf'  => __('PDF'),
                    ])
                    ->default('pdf')
                    ->required(),
                TextInput::make('title')
                    ->label(__('admin.fields.title'))
                    ->required(),
            ])
            ->action(function (Model $record, array $data, DocumentServiceContract $documentService): RedirectResponse|Response {
                try {
                    // Enforce the active scope at read-time as a defence-in-depth guard against crafted payloads.
                    $template = DocumentTemplate::query()
                        ->active()
                        ->findOrFail($data['template_id']);

                    $document = $documentService->generateDocument(
                        template: $template,
                        relatedModel: $record,
                        variables: self::getDefaultVariables($record),
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

    private static function getDefaultVariables(Model $record): array
    {
        $now = now();

        $variables = [
            'MODEL_ID'   => $record->getKey(),
            'MODEL_TYPE' => $record->getMorphClass(),
            // Prefer model timestamps when available and gracefully fall back to the current time.
            'CREATED_AT' => self::formatDateTime($record->getAttribute('created_at')) ?? $now->format('d/m/Y H:i'),
            'UPDATED_AT' => self::formatDateTime($record->getAttribute('updated_at')) ?? $now->format('d/m/Y H:i'),
        ];

        // Add model-specific variables if the model has common attributes
        if (method_exists($record, 'getAttribute')) {
            $commonAttributes = ['name', 'title', 'code', 'description', 'status'];

            foreach ($commonAttributes as $attribute) {
                $value = $record->getAttribute($attribute);

                if ($value !== null) {
                    $variables[strtoupper($attribute)] = $value;
                }
            }
        }

        return $variables;
    }

    /**
     * Normalize various date/time inputs to a consistent string representation for template consumption.
     */
    private static function formatDateTime(mixed $value): ?string
    {
        // Guard against missing values and unexpected data types returned by the model attributes.
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            // Normalise native \DateTimeInterface values via Carbon to honour application timezone settings.
            return Carbon::instance($value)->format('d/m/Y H:i');
        }

        if (is_numeric($value)) {
            // Treat raw integers/floats as UNIX timestamps to support casted database columns.
            return Carbon::createFromTimestamp((int) $value)->format('d/m/Y H:i');
        }

        if (is_string($value) && $value !== '') {
            try {
                // Delegate parsing of arbitrary string values (e.g. database casts) to Carbon for consistency.
                return Carbon::parse($value)->format('d/m/Y H:i');
            } catch (Throwable) {
                // Invalid strings should not break document generation; fall through to the null response.
            }
        }

        return null;
    }
}
