<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class TranslationsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Translations';

    protected static ?string $modelLabel = 'Translation';

    protected static ?string $pluralModelLabel = 'Translations';

    public function form(Schema $form): Schema
    {
        return $schema->schema([
            Section::make(__('translations.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('locale')
                                ->label(__('translations.locale'))
                                ->options([
                                    'en' => 'English',
                                    'lt' => 'Lietuvių',
                                ])
                                ->required()
                                ->searchable(),
                            TextInput::make('name')
                                ->label(__('translations.name'))
                                ->required()
                                ->maxLength(255),
                        ]),
                    Textarea::make('description')
                        ->label(__('translations.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('short_description')
                        ->label(__('translations.short_description'))
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('translations.seo'))
                ->schema([
                    TextInput::make('seo_title')
                        ->label(__('translations.seo_title'))
                        ->maxLength(255),
                    Textarea::make('seo_description')
                        ->label(__('translations.seo_description'))
                        ->rows(2)
                        ->maxLength(500),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->columns([
                TextColumn::make('locale')
                    ->label(__('translations.locale'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'en'    => 'success',
                        'lt'    => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'en'    => 'English',
                        'lt'    => 'Lietuvių',
                        default => $state,
                    }),
                TextColumn::make('name')
                    ->label(__('translations.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('description')
                    ->label(__('translations.description'))
                    ->limit(50)
                    ->tooltip(static function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('short_description')
                    ->label(__('translations.short_description'))
                    ->limit(30)
                    ->tooltip(static function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('seo_title')
                    ->label(__('translations.seo_title'))
                    ->limit(30)
                    ->tooltip(static function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('translations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('locale')
                    ->label(__('translations.locale'))
                    ->options([
                        'en' => 'English',
                        'lt' => 'Lietuvių',
                    ]),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit translations')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit category translations')
                    ->modalWidth('5xl')
                    // Provide an inline translation editor for category locales without leaving the relation manager.
                    ->configureRepeater(static function (Repeater $repeater): Repeater {
                        return $repeater
                            ->collapsible()
                            ->defaultItems(0)
                            ->schema([
                                Hidden::make('id'),
                                Select::make('locale')
                                    ->label(__('translations.locale'))
                                    ->options([
                                        'en' => 'English',
                                        'lt' => 'Lietuvių',
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->disabled(fn (callable $get): bool => filled($get('id')))
                                    ->dehydrated(true),
                                TextInput::make('name')
                                    ->label(__('translations.name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->label(__('translations.slug'))
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->label(__('translations.description'))
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Textarea::make('short_description')
                                    ->label(__('translations.short_description'))
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                TextInput::make('seo_title')
                                    ->label(__('translations.seo_title'))
                                    ->maxLength(255),
                                Textarea::make('seo_description')
                                    ->label(__('translations.seo_description'))
                                    ->rows(2)
                                    ->maxLength(500),
                            ]);
                    }),
                CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                // Add bulk actions if needed
            ])
            ->defaultSort('locale');
    }
}