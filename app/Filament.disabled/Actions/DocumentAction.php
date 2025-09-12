<?php declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\DocumentTemplate;
use App\Services\DocumentService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Closure;

final class DocumentAction extends Action
{
    private array $variables = [];
    private ?Closure $variablesCallback = null;

    public static function getDefaultName(): ?string
    {
        return 'generate_document';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('documents.generate_document'))
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->modalHeading(__('documents.generate_document'))
            ->modalSubmitActionLabel(__('documents.generate'))
            ->form([
                Forms\Components\Select::make('template_id')
                    ->label(__('documents.template'))
                    ->options(
                        DocumentTemplate::active()
                            ->pluck('name', 'id')
                            ->filter(fn($label) => filled($label))
                            ->toArray()
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('title')
                    ->label(__('documents.title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->label(__('documents.notes'))
                    ->maxLength(1000),
            ])
            ->action(function (array $data, Model $record) {
                $template = DocumentTemplate::findOrFail($data['template_id']);
                $service = app(DocumentService::class);

                // Get variables from callback or default
                $variables = $this->variablesCallback
                    ? call_user_func($this->variablesCallback, $record)
                    : $this->extractVariablesFromRecord($record);

                // Generate the document
                $document = $service->generateDocument(
                    template: $template,
                    relatedModel: $record,
                    variables: $variables,
                    title: $data['title']
                );

                Notification::make()
                    ->title(__('documents.document_generated'))
                    ->success()
                    ->send();

                // Redirect to view the document
                return redirect()->route('filament.admin.resources.documents.view', $document);
            });
    }

    public function variables(array|Closure $variables): static
    {
        if (is_callable($variables)) {
            $this->variablesCallback = $variables;
        } else {
            $this->variables = $variables;
        }

        return $this;
    }

    private function extractVariablesFromRecord(Model $record): array
    {
        $service = app(DocumentService::class);
        $variables = $service->getAvailableVariables();

        // Extract model-specific variables
        $modelVariables = $service->extractVariablesFromModel($record, class_basename($record) . '_');

        // Merge with global variables
        return array_merge($variables, $modelVariables, $this->variables);
    }
}
