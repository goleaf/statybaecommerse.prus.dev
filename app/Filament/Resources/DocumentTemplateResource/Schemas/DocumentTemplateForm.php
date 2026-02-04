<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTemplateResource\Schemas;

use App\Enums\DocumentTemplateCategory;
use App\Enums\DocumentTemplateType;
use App\Models\DocumentTemplate;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DocumentTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.document_templates.sections.basic_information'))
                ->description(__('admin.document_templates.sections.basic_information_description'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('messages.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                            TextInput::make('slug')
                                ->label(__('messages.slug'))
                                ->required()
                                ->maxLength(255)
                                ->unique(DocumentTemplate::class, 'slug', ignoreRecord: true),
                        ]),
                    Textarea::make('description')
                        ->label(__('messages.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Grid::make(3)
                        ->schema([
                            Select::make('type')
                                ->label(__('admin.document_templates.fields.type'))
                                ->options(DocumentTemplateType::options())
                                ->required()
                                ->searchable(),
                            Select::make('category')
                                ->label(__('admin.document_templates.fields.category'))
                                ->options(DocumentTemplateCategory::options())
                                ->searchable()
                                ->nullable(),
                            Toggle::make('is_active')
                                ->label(__('admin.document_templates.fields.is_active'))
                                ->default(true),
                        ]),
                ]),
            Section::make(__('admin.document_templates.sections.content'))
                ->description(__('admin.document_templates.sections.content_description'))
                ->schema([
                    Textarea::make('content')
                        ->label(__('admin.document_templates.fields.content'))
                        ->required()
                        ->rows(16)
                        ->columnSpanFull(),
                ]),
            Section::make(__('admin.document_templates.sections.variables'))
                ->description(__('admin.document_templates.sections.variables_description'))
                ->schema([
                    KeyValue::make('variables')
                        ->label(__('admin.document_templates.fields.variables'))
                        ->default([])
                        ->columnSpanFull(),
                ]),
            Section::make(__('admin.document_templates.sections.settings'))
                ->description(__('admin.document_templates.sections.settings_description'))
                ->schema([
                    KeyValue::make('settings')
                        ->label(__('admin.document_templates.fields.settings'))
                        ->default([])
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
