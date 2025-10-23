<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
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

    public function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return $form->schema([
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
        // Filament 4 expects returning the Table builder instance.
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
                    ->tooltip(static function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return is_string($state) && mb_strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('description')
                    ->label(__('system_setting_categories.translations.description'))
                    ->limit(100)
                    ->tooltip(static function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return is_string($state) && mb_strlen($state) > 100 ? $state : null;
                    })
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
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('locale');
    }
}
