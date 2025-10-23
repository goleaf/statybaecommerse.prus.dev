<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

final class ChildrenRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $title = 'Subcategories';

    protected static ?string $modelLabel = 'Subcategory';

    protected static ?string $pluralModelLabel = 'Subcategories';

    public function form(Schema $form): Schema
    {
        // Bridge the relation manager form to the Schema-based builder expected by Filament v4.
        return $form->schema([
            Section::make(__('categories.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('categories.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                            TextInput::make('slug')
                                ->label(__('categories.slug'))
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('categories.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('short_description')
                        ->label(__('categories.short_description'))
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('categories.appearance'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            ColorPicker::make('color')
                                ->label(__('categories.color'))
                                ->hex(),
                            TextInput::make('sort_order')
                                ->label(__('categories.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            TextInput::make('product_limit')
                                ->label(__('categories.product_limit'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('categories.product_limit_help')),
                        ]),
                ]),
            Section::make(__('categories.settings'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('categories.is_active'))
                                ->default(true),
                            Toggle::make('is_visible')
                                ->label(__('categories.is_visible'))
                                ->default(true),
                            Toggle::make('is_enabled')
                                ->label(__('categories.is_enabled'))
                                ->default(true),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_featured')
                                ->label(__('categories.is_featured')),
                            Toggle::make('show_in_menu')
                                ->label(__('categories.show_in_menu'))
                                ->default(true),
                        ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('categories.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label(__('categories.slug'))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('color')
                    ->label(__('categories.color'))
                    ->toggleable(),
                TextColumn::make('products_count')
                    ->label(__('categories.products_count'))
                    ->counts('products')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('children_count')
                    ->label(__('categories.subcategories_count'))
                    ->counts('children')
                    ->badge()
                    ->color('info'),
                TextColumn::make('sort_order')
                    ->label(__('categories.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_active')
                    ->label(__('categories.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_visible')
                    ->label(__('categories.is_visible'))
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_enabled')
                    ->label(__('categories.is_enabled'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_featured')
                    ->label(__('categories.is_featured'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->label(__('categories.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->trueLabel(__('categories.active_only'))
                    ->falseLabel(__('categories.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_visible')
                    ->trueLabel(__('categories.visible_only'))
                    ->falseLabel(__('categories.hidden_only'))
                    ->native(false),
                TernaryFilter::make('is_enabled')
                    ->trueLabel(__('categories.enabled_only'))
                    ->falseLabel(__('categories.disabled_only'))
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->trueLabel(__('categories.featured_only'))
                    ->falseLabel(__('categories.not_featured'))
                    ->native(false),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
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
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}