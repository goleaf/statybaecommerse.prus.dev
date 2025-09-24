<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\RelationManagers;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class SettingsRelationManager extends RelationManager
{
    protected static string $relationship = 'settings';

    protected static ?string $title = 'Settings';

    protected static ?string $modelLabel = 'Setting';

    protected static ?string $pluralModelLabel = 'Settings';

    public function form(Schema $schema): Schema
    {
        return $form->schema([
            Section::make(__('system_setting_categories.settings.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('key')
                                ->label(__('system_setting_categories.settings.key'))
                                ->required()
                                ->maxLength(255)
                                ->live()
                                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Str::slug($state)))
                                ->helperText(__('system_setting_categories.settings.key_help')),

                            TextInput::make('slug')
                                ->label(__('system_setting_categories.settings.slug'))
                                ->required()
                                ->maxLength(255)
                                ->helperText(__('system_setting_categories.settings.slug_help')),
                        ]),

                    TextInput::make('name')
                        ->label(__('system_setting_categories.settings.name'))
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('system_setting_categories.settings.name_help')),

                    Textarea::make('description')
                        ->label(__('system_setting_categories.settings.description'))
                        ->rows(3)
                        ->helperText(__('system_setting_categories.settings.description_help')),
                ]),

            Section::make(__('system_setting_categories.settings.configuration'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('system_setting_categories.settings.type'))
                                ->options([
                                    'string' => 'String',
                                    'integer' => 'Integer',
                                    'boolean' => 'Boolean',
                                    'array' => 'Array',
                                    'json' => 'JSON',
                                    'text' => 'Text',
                                    'email' => 'Email',
                                    'url' => 'URL',
                                    'date' => 'Date',
                                    'datetime' => 'DateTime',
                                ])
                                ->required()
                                ->default('string')
                                ->native(false),

                            TextInput::make('default_value')
                                ->label(__('system_setting_categories.settings.default_value'))
                                ->maxLength(255)
                                ->helperText(__('system_setting_categories.settings.default_value_help')),
                        ]),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('system_setting_categories.settings.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->helperText(__('system_setting_categories.settings.sort_order_help')),

                            Toggle::make('is_active')
                                ->label(__('system_setting_categories.settings.is_active'))
                                ->default(true)
                                ->helperText(__('system_setting_categories.settings.is_active_help')),
                        ]),
                ]),

            Section::make(__('system_setting_categories.settings.validation'))
                ->schema([
                    Textarea::make('validation_rules')
                        ->label(__('system_setting_categories.settings.validation_rules'))
                        ->rows(3)
                        ->helperText(__('system_setting_categories.settings.validation_rules_help')),

                    Textarea::make('options')
                        ->label(__('system_setting_categories.settings.options'))
                        ->rows(3)
                        ->helperText(__('system_setting_categories.settings.options_help')),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('system_setting_categories.settings.key'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label(__('system_setting_categories.settings.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('type')
                    ->label(__('system_setting_categories.settings.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string' => 'primary',
                        'integer' => 'info',
                        'boolean' => 'warning',
                        'array' => 'success',
                        'json' => 'danger',
                        'text' => 'secondary',
                        'email' => 'info',
                        'url' => 'primary',
                        'date' => 'success',
                        'datetime' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('default_value')
                    ->label(__('system_setting_categories.settings.default_value'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->label(__('system_setting_categories.settings.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label(__('system_setting_categories.settings.is_active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label(__('system_setting_categories.settings.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('system_setting_categories.settings.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('system_setting_categories.settings.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('system_setting_categories.settings.type'))
                    ->options([
                        'string' => 'String',
                        'integer' => 'Integer',
                        'boolean' => 'Boolean',
                        'array' => 'Array',
                        'json' => 'JSON',
                        'text' => 'Text',
                        'email' => 'Email',
                        'url' => 'URL',
                        'date' => 'Date',
                        'datetime' => 'DateTime',
                    ])
                    ->native(false),

                TernaryFilter::make('is_active')
                    ->trueLabel(__('system_setting_categories.settings.active_only'))
                    ->falseLabel(__('system_setting_categories.settings.inactive_only'))
                    ->native(false),
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
                DeleteBulkAction::make(),
            ])
            ->defaultSort('sort_order');
    }
}
