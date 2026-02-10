<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributes';

    protected static ?string $recordTitleAttribute = 'value';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.attributes');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('attribute_id')
                    ->label(__('messages.attribute'))
                    ->relationship('attribute', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('attribute_value_id')
                    ->label(__('messages.attribute_value'))
                    ->relationship('attributeValue', 'value')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('attribute.name')
                    ->label(__('messages.attribute'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('value')
                    ->label(__('messages.attribute_value'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
