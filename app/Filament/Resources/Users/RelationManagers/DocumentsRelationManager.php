<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\DocumentTemplateCategory;
use App\Enums\DocumentTemplateType;
use App\Models\Document;
use App\Models\DocumentTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('document_template_id')
                    ->relationship(
                        name: 'documentTemplate',
                        titleAttribute: 'name',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->options(DocumentTemplateType::options())
                            ->default(DocumentTemplateType::Document->value)
                            ->required(),
                        Select::make('category')
                            ->options(DocumentTemplateCategory::options())
                            ->default(DocumentTemplateCategory::Business->value)
                            ->required(),
                        Textarea::make('content')
                            ->default('<h1>{{title}}</h1>')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->createOptionUsing(static function (array $data): int {
                        $template = DocumentTemplate::query()->create([
                            'name'        => (string) ($data['name'] ?? 'Document template'),
                            'content'     => (string) ($data['content'] ?? '<h1>{{title}}</h1>'),
                            'type'        => (string) ($data['type'] ?? DocumentTemplateType::Document->value),
                            'category'    => (string) ($data['category'] ?? DocumentTemplateCategory::Business->value),
                            'is_active'   => true,
                            'description' => 'Auto-created from user relation manager',
                            'variables'   => ['title'],
                        ]);

                        return (int) $template->getKey();
                    }),
                TextInput::make('title')
                    ->required()
                    ->default('Document')
                    ->maxLength(255),
                Textarea::make('content')
                    ->required()
                    ->default('<h1>{{title}}</h1>')
                    ->columnSpanFull(),
                Select::make('format')
                    ->options($this->formatOptions())
                    ->default(Document::FORMAT_HTML)
                    ->required(),
                TextInput::make('file_path')
                    ->maxLength(255),
                Select::make('status')
                    ->options($this->statusOptions())
                    ->default(Document::STATUS_DRAFT)
                    ->required()
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('documentTemplate.name')
                    ->label('Template')
                    ->sortable(),
                TextColumn::make('file_path')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->normalizeDocumentData($data))
                    ->using(function (array $data): Document {
                        $payload = $this->normalizeDocumentData($data);
                        $ownerRecord = $this->getOwnerRecord();

                        $payload['documentable_type'] = $ownerRecord::class;
                        $payload['documentable_id'] = (int) $ownerRecord->getKey();
                        $payload['created_by'] = null;
                        $payload['updated_by'] = null;

                        return Document::withoutGlobalScopes()->create($payload);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->normalizeDocumentData($data)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeDocumentData(array $data): array
    {
        $template = null;

        if (isset($data['document_template_id'])) {
            $template = DocumentTemplate::query()
                ->withoutGlobalScopes()
                ->find((int) $data['document_template_id']);
        }

        if (! $template instanceof DocumentTemplate) {
            $template = $this->resolveFallbackTemplate();
        }

        $content = trim((string) ($data['content'] ?? ''));
        if ($content === '' && $template instanceof DocumentTemplate) {
            $content = (string) $template->content;
        }

        $status = (string) ($data['status'] ?? Document::STATUS_DRAFT);
        if (! in_array($status, array_keys($this->statusOptions()), true)) {
            $status = Document::STATUS_DRAFT;
        }

        $format = (string) ($data['format'] ?? Document::FORMAT_HTML);
        if (! in_array($format, array_keys($this->formatOptions()), true)) {
            $format = Document::FORMAT_HTML;
        }

        $data['content'] = $content !== '' ? $content : '<h1>{{title}}</h1>';
        $data['document_template_id'] = (int) $template->getKey();
        $data['title'] = trim((string) ($data['title'] ?? '')) !== ''
            ? trim((string) $data['title'])
            : 'Document';
        $data['type'] = (string) ($data['type'] ?? ($template?->type ?? DocumentTemplateType::Document->value));
        $data['format'] = $format;
        $data['status'] = $status;

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function formatOptions(): array
    {
        return [
            Document::FORMAT_HTML => 'HTML',
            Document::FORMAT_PDF  => 'PDF',
            Document::FORMAT_DOCX => 'DOCX',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return [
            Document::STATUS_DRAFT     => 'Draft',
            Document::STATUS_GENERATED => 'Generated',
            Document::STATUS_PUBLISHED => 'Published',
            Document::STATUS_ARCHIVED  => 'Archived',
        ];
    }

    private function resolveFallbackTemplate(): DocumentTemplate
    {
        $existingTemplate = DocumentTemplate::query()
            ->withoutGlobalScopes()
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->first();

        if ($existingTemplate instanceof DocumentTemplate) {
            return $existingTemplate;
        }

        $suffix = strtolower(Str::random(8));

        return DocumentTemplate::query()
            ->withoutGlobalScopes()
            ->create([
                'name'        => 'Auto Template ' . strtoupper($suffix),
                'slug'        => 'auto-template-' . $suffix,
                'description' => 'Auto-created fallback template for user documents.',
                'content'     => '<h1>{{title}}</h1>',
                'variables'   => ['title'],
                'type'        => DocumentTemplateType::Document->value,
                'category'    => DocumentTemplateCategory::Business->value,
                'settings'    => null,
                'is_active'   => true,
            ]);
    }
}
