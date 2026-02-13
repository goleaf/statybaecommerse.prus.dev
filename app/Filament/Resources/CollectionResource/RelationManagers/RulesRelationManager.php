<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RulesRelationManager extends RelationManager
{
    protected static string $relationship = 'rules';

    protected static ?string $recordTitleAttribute = 'field';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.collections.rules');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('field')
                    ->label(__('admin.collections.rule_column'))
                    ->options([
                        'name'  => 'Product Name',
                        'price' => 'Product Price',
                        'sku'   => 'Product SKU',
                    ])
                    ->required(),
                Select::make('operator')
                    ->label(__('admin.collections.rule_operator'))
                    ->options([
                        '='        => 'Equals',
                        '!='       => 'Not Equals',
                        '>'        => 'Greater Than',
                        '<'        => 'Less Than',
                        'contains' => 'Contains',
                    ])
                    ->required(),
                TextInput::make('value')
                    ->label(__('admin.collections.rule_value'))
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('field')
                    ->label(__('admin.collections.rule_column'))
                    ->sortable(),
                TextColumn::make('operator')
                    ->sortable()
                    ->label(__('admin.collections.rule_operator')),
                TextColumn::make('value')
                    ->sortable()
                    ->label(__('admin.collections.rule_value')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
