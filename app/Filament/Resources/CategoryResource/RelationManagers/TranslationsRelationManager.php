<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Translations';

    protected static ?string $modelLabel = 'Translation';

    protected static ?string $pluralModelLabel = 'Translations';

    public function form(Schema $schema): Schema
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
        return $table
            ->columns([
                TextColumn::make('locale')
                    ->label(__('translations.locale'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'en' => 'success',
                        'lt' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'en' => 'English',
                        'lt' => 'Lietuvių',
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
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('short_description')
                    ->label(__('translations.short_description'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('seo_title')
                    ->label(__('translations.seo_title'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
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
