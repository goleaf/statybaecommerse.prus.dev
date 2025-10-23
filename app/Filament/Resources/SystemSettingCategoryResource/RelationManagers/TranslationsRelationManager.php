<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;

final class TranslationsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Translations';

    protected static ?string $modelLabel = 'Translation';

    protected static ?string $pluralModelLabel = 'Translations';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('system_setting_categories.translations.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('locale')
                                ->label(__('system_setting_categories.translations.locale'))
                                ->options([
                                    'lt' => 'Lithuanian',
                                    'en' => 'English',
                                    'de' => 'German',
                                    'fr' => 'French',
                                    'es' => 'Spanish',
                                    'it' => 'Italian',
                                    'pl' => 'Polish',
                                    'ru' => 'Russian',
                                ])
                                ->required()
                                ->searchable()
                                ->native(false),
                        ]),

                    TextInput::make('name')
                        ->label(__('system_setting_categories.translations.name'))
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),

                    Textarea::make('description')
                        ->label(__('system_setting_categories.translations.description'))
                        ->maxLength(1000)
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('locale')
                    ->label(__('system_setting_categories.translations.locale'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lt'    => 'info',
                        'en'    => 'success',
                        'de'    => 'warning',
                        'fr'    => 'danger',
                        'es'    => 'primary',
                        'it'    => 'secondary',
                        'pl'    => 'gray',
                        'ru'    => 'dark',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('name')
                    ->label(__('system_setting_categories.translations.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn (TextColumn $column): ?string => (is_string($state = $column->getState()) && strlen($state) > 50)
                        ? $state
                        : null),

                TextColumn::make('description')
                    ->label(__('system_setting_categories.translations.description'))
                    ->limit(100)
                    ->tooltip(fn (TextColumn $column): ?string => (is_string($state = $column->getState()) && strlen($state) > 100)
                        ? $state
                        : null)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('system_setting_categories.translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('system_setting_categories.translations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('locale')
                    ->label(__('system_setting_categories.translations.locale'))
                    ->options([
                        'lt' => 'Lithuanian',
                        'en' => 'English',
                        'de' => 'German',
                        'fr' => 'French',
                        'es' => 'Spanish',
                        'it' => 'Italian',
                        'pl' => 'Polish',
                        'ru' => 'Russian',
                    ])
                    ->native(false),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('locale');
    }
}
