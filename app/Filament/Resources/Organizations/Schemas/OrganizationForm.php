<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Schemas;

use App\Enums\OrganizationType;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                        Hidden::make('slug')
                            ->dehydrateStateUsing(fn (Get $get): string => Str::slug((string) $get('name')))
                            ->unique(ignoreRecord: true),
                        Select::make('type')
                            ->label(__('messages.type'))
                            ->options(OrganizationType::options())
                            ->required()
                            ->default(OrganizationType::COMPANY->value)
                            ->searchable(),
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make(__('messages.description'))
                    ->schema([
                        RichEditor::make('description')
                            ->label(__('messages.description'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

            ]);
    }
}
