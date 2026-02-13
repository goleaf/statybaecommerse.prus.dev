<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\DocumentTemplateCategory;
use App\Enums\DocumentTemplateType;
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
                        modifyQueryUsing: static fn ($query) => $query->orderBy('name'),
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
                    })
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('content')
                    ->required()
                    ->default('<h1>{{title}}</h1>')
                    ->columnSpanFull(),
                Select::make('format')
                    ->options([
                        'html' => 'HTML',
                        'pdf'  => 'PDF',
                        'docx' => 'DOCX',
                    ])
                    ->default('html')
                    ->required(),
                TextInput::make('file_path')
                    ->maxLength(255),
                TextInput::make('status')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
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
                    ->mutateDataUsing(static function (array $data): array {
                        $template = null;

                        if (isset($data['document_template_id'])) {
                            $template = DocumentTemplate::query()->find((int) $data['document_template_id']);
                        }

                        $content = trim((string) ($data['content'] ?? ''));
                        if ($content === '' && $template instanceof DocumentTemplate) {
                            $content = (string) $template->content;
                        }

                        $data['content'] = $content !== '' ? $content : '<h1>{{title}}</h1>';
                        $data['type'] = (string) ($data['type'] ?? ($template?->type ?? DocumentTemplateType::Document->value));
                        $data['format'] = (string) ($data['format'] ?? 'html');
                        $data['status'] = (string) ($data['status'] ?? 'draft');

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
