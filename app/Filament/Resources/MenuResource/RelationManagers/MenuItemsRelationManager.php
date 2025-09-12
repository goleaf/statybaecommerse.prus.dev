<?php declare(strict_types=1);

namespace App\Filament\Resources\MenuResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

final class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'allItems';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Forms\Components\Select::make('parent_id')
                ->label(__('translations.parent_item'))
                ->relationship('parent', 'label')
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\TextInput::make('label')
                ->label(__('translations.label'))
                ->required(),
            Forms\Components\TextInput::make('url')
                ->label(__('translations.url')),
            Forms\Components\TextInput::make('route_name')
                ->label(__('translations.route_name')),
            Forms\Components\KeyValue::make('route_params')
                ->label(__('translations.route_params'))
                ->columnSpanFull(),
            Forms\Components\TextInput::make('icon')
                ->label(__('translations.icon')),
            Forms\Components\TextInput::make('sort_order')
                ->label(__('translations.sort_order'))
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_visible')
                ->label(__('translations.visible'))
                ->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('label')->label(__('translations.label'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('parent.label')->label(__('translations.parent_item'))->sortable(),
                Tables\Columns\TextColumn::make('route_name')->label(__('translations.route_name'))->toggleable(),
                Tables\Columns\TextColumn::make('url')->label(__('translations.url'))->toggleable(),
                Tables\Columns\ToggleColumn::make('is_visible')->label(__('translations.visible'))->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
