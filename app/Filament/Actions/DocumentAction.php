<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Contracts\DocumentServiceContract;
use App\Models\DocumentTemplate;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

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
                    ->required(),
            ])
            ->action(function (Model $record, array $data, DocumentServiceContract $documentService): RedirectResponse|Response {
                try {
                    $template = DocumentTemplate::query()->findOrFail($data['template_id']);

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

    private static function getDefaultVariables(Model $record): array
    {
        $now = now();

        $variables = [
            'MODEL_ID'   => $record->getKey(),
            'MODEL_TYPE' => $record->getMorphClass(),
            'CREATED_AT' => $now->format('d/m/Y H:i'),
            'UPDATED_AT' => $now->format('d/m/Y H:i'),
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
}
