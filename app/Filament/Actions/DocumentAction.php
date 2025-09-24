<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Services\DocumentService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

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
                        'pdf' => __('PDF'),
                    ])
                    ->default('pdf')
                    ->required(),
                TextInput::make('title')
                    ->label(__('admin.fields.title'))
                    ->required(),
            ])
            ->action(function ($record, array $data, DocumentService $documentService) {
                try {
                    $document = $documentService->generateDocument(
                        templateId: $data['template_id'],
                        documentable: $record,
                        variables: $this->getDefaultVariables($record),
                        format: $data['format'],
                        title: $data['title']
                    );

                    Notification::make()
                        ->title(__('admin.notifications.document_generated'))
                        ->body(__('admin.notifications.document_generated_successfully'))
                        ->success()
                        ->send();

                    if ($data['format'] === 'pdf') {
                        return response()->download($document->file_path, $document->title.'.pdf');
                    }

                    return response($document->content, 200, [
                        'Content-Type' => 'text/html',
                    ]);
                } catch (\Exception $e) {
                    Notification::make()
                        ->title(__('admin.notifications.document_generation_failed'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    private static function getDefaultVariables($record): array
    {
        $variables = [
            'MODEL_ID' => $record->getKey(),
            'MODEL_TYPE' => $record->getMorphClass(),
            'CREATED_AT' => now()->format('d/m/Y H:i'),
            'UPDATED_AT' => now()->format('d/m/Y H:i'),
        ];

        // Add model-specific variables if the model has common attributes
        if (method_exists($record, 'getAttribute')) {
            $commonAttributes = ['name', 'title', 'code', 'description', 'status'];

            foreach ($commonAttributes as $attribute) {
                if ($record->getAttribute($attribute)) {
                    $variables[strtoupper($attribute)] = $record->getAttribute($attribute);
                }
            }
        }

        return $variables;
    }
}
