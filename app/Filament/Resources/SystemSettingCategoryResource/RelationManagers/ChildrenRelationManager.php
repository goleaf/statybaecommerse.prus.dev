<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\RelationManagers;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
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
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $title = 'Sub Categories';

    protected static ?string $modelLabel = 'Sub Category';

    protected static ?string $pluralModelLabel = 'Sub Categories';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('system_setting_categories.children.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('system_setting_categories.children.name'))
                                ->required()
                                ->maxLength(255)
                                ->live()
                                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Str::slug($state)))
                                ->helperText(__('system_setting_categories.children.name_help')),
                            TextInput::make('slug')
                                ->label(__('system_setting_categories.children.slug'))
                                ->required()
                                ->maxLength(255)
                                ->helperText(__('system_setting_categories.children.slug_help')),
                        ]),
                    Textarea::make('description')
                        ->label(__('system_setting_categories.children.description'))
                        ->rows(3)
                        ->helperText(__('system_setting_categories.children.description_help')),
                ]),
            Section::make(__('system_setting_categories.children.appearance'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('icon')
                                ->label(__('system_setting_categories.children.icon'))
                                ->maxLength(255)
                                ->placeholder('heroicon-o-cog-6-tooth')
                                ->helperText(__('system_setting_categories.children.icon_help')),
                            ColorPicker::make('color')
                                ->label(__('system_setting_categories.children.color'))
                                ->helperText(__('system_setting_categories.children.color_help')),
                        ]),
                ]),
            Section::make(__('system_setting_categories.children.configuration'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('sort_order')
                                ->label(__('system_setting_categories.children.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->helperText(__('system_setting_categories.children.sort_order_help')),
                            Toggle::make('is_active')
                                ->label(__('system_setting_categories.children.is_active'))
                                ->default(true)
                                ->helperText(__('system_setting_categories.children.is_active_help')),
                        ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('system_setting_categories.children.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label(__('system_setting_categories.children.slug'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                TextColumn::make('description')
                    ->label(__('system_setting_categories.children.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('icon')
                    ->label(__('system_setting_categories.children.icon'))
                    ->formatStateUsing(fn ($state) => $state ?: 'heroicon-o-cog-6-tooth')
                    ->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('color')
                    ->label(__('system_setting_categories.children.color'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('settings_count')
                    ->label(__('system_setting_categories.children.settings_count'))
                    ->counts('settings')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('active_settings_count')
                    ->label(__('system_setting_categories.children.active_settings_count'))
                    ->counts(['settings' => fn ($query) => $query->where('is_active', true)])
                    ->sortable()
                    ->badge()
                    ->color('success'),
                IconColumn::make('is_active')
                    ->label(__('system_setting_categories.children.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('system_setting_categories.children.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('system_setting_categories.children.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('system_setting_categories.children.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->trueLabel(__('system_setting_categories.children.active_only'))
                    ->falseLabel(__('system_setting_categories.children.inactive_only'))
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
