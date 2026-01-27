<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class FeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'features';

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->withoutGlobalScopes();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('feature_type')
                ->label(__('admin.products.feature_type'))
                ->options([
                    'specification' => __('admin.products.feature_specification'),
                    'benefit' => __('admin.products.feature_benefit'),
                    'performance' => __('admin.products.feature_performance'),
                    'other' => __('admin.products.feature_other'),
                ])
                ->default('specification')
                ->required(),
            TextInput::make('feature_key')
                ->label(__('admin.products.feature_key'))
                ->required()
                ->maxLength(255),
            TextInput::make('feature_value')
                ->label(__('admin.products.feature_value'))
                ->numeric()
                ->required(),
            TextInput::make('weight')
                ->label(__('admin.products.weight'))
                ->numeric()
                ->default(1),
            Toggle::make('is_active')
                ->label(__('admin.products.is_active'))
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('feature_key')
            ->columns([
                BadgeColumn::make('feature_type')
                    ->label(__('admin.products.feature_type'))
                    ->formatStateUsing(static fn (?string $state): string => ucfirst((string) $state))
                    ->colors([
                        'primary' => 'specification',
                        'success' => 'benefit',
                        'warning' => 'performance',
                        'gray' => 'other',
                    ])
                    ->sortable(),
                TextColumn::make('feature_key')
                    ->label(__('admin.products.feature_key'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('feature_value')
                    ->label(__('admin.products.feature_value'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight')
                    ->label(__('admin.products.weight'))
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.products.is_active')),
            ])
            ->headerActions([
                CreateAction::make(),
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
