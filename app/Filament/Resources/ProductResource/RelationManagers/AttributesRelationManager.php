<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class AttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributes';

    protected static ?string $title = 'Product Attributes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('attribute_id')
                    ->label(__('products.attributes.attribute'))
                    ->relationship('attribute', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500),
                        Forms\Components\Select::make('type')
                            ->options([
                                'text' => __('attributes.types.text'),
                                'number' => __('attributes.types.number'),
                                'boolean' => __('attributes.types.boolean'),
                                'select' => __('attributes.types.select'),
                                'multiselect' => __('attributes.types.multiselect'),
                                'date' => __('attributes.types.date'),
                                'file' => __('attributes.types.file'),
                            ])
                            ->required(),
                    ]),

                Forms\Components\TextInput::make('value')
                    ->label(__('products.attributes.value'))
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label(__('products.attributes.description'))
                    ->maxLength(500),

                Forms\Components\Toggle::make('is_visible')
                    ->label(__('products.attributes.is_visible'))
                    ->default(true),

                Forms\Components\Toggle::make('is_filterable')
                    ->label(__('products.attributes.is_filterable'))
                    ->default(false),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('products.attributes.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('attribute.name')
            ->columns([
                Tables\Columns\TextColumn::make('attribute.name')
                    ->label(__('products.attributes.attribute'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('attribute.type')
                    ->label(__('products.attributes.type'))
                    ->formatStateUsing(fn (string $state): string => __("attributes.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'text' => 'blue',
                        'number' => 'green',
                        'boolean' => 'orange',
                        'select' => 'purple',
                        'multiselect' => 'pink',
                        'date' => 'cyan',
                        'file' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('value')
                    ->label(__('products.attributes.value'))
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('products.attributes.is_visible'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_filterable')
                    ->label(__('products.attributes.is_filterable'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('products.attributes.sort_order'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('products.attributes.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('attribute_type')
                    ->label(__('products.attributes.attribute_type'))
                    ->relationship('attribute', 'type')
                    ->options([
                        'text' => __('attributes.types.text'),
                        'number' => __('attributes.types.number'),
                        'boolean' => __('attributes.types.boolean'),
                        'select' => __('attributes.types.select'),
                        'multiselect' => __('attributes.types.multiselect'),
                        'date' => __('attributes.types.date'),
                        'file' => __('attributes.types.file'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('products.attributes.is_visible'))
                    ->boolean()
                    ->trueLabel(__('products.attributes.visible_only'))
                    ->falseLabel(__('products.attributes.hidden_only'))
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_filterable')
                    ->label(__('products.attributes.is_filterable'))
                    ->boolean()
                    ->trueLabel(__('products.attributes.filterable_only'))
                    ->falseLabel(__('products.attributes.non_filterable_only'))
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                EditAction::make(),
                Tables\Actions\DetachAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
