<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.document_templates.document_form.sections.organization'))
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
                            ->unique(ignoreRecord: true),
                        TextInput::make('type')
                            ->label(__('messages.Type'))
                            ->required()
                            ->maxLength(255),
                    ])->columns(3)
                    ->columnSpanFull(),

                Section::make(__('messages.Description'))
                    ->schema([
                        RichEditor::make('description')
                            ->label(__('messages.Description'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(__('admin.navigation.settings'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('messages.active'))
                            ->required(),
                        KeyValue::make('settings')
                            ->label(__('messages.Value')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
