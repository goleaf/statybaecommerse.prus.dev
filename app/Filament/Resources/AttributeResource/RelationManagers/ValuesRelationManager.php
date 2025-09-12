<?php declare(strict_types=1);

namespace App\Filament\Resources\AttributeResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

final class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('value')
                ->label(__('translations.value'))
                ->required(),
            Forms\Components\TextInput::make('slug')
                ->label(__('translations.slug'))
                ->required(),
            Forms\Components\ColorPicker::make('color_code')
                ->label(__('translations.color')),
            Forms\Components\TextInput::make('sort_order')
                ->label(__('translations.sort_order'))
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_enabled')
                ->label(__('translations.enabled'))
                ->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                Tables\Columns\TextColumn::make('value')->label(__('translations.value'))->searchable(),
                Tables\Columns\TextColumn::make('slug')->label(__('translations.slug'))->toggleable(),
                Tables\Columns\TextColumn::make('color_code')->label(__('translations.color'))->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')->label(__('translations.sort_order'))->numeric()->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')->label(__('translations.enabled'))->boolean(),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
