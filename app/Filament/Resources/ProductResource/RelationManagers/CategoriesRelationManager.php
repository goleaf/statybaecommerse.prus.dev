<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * CategoriesRelationManager
 * 
 * Filament resource for admin panel management.
 */
class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $title = 'Categories';

    public function form(Form $form): Form
    {
        return $schema->schema([
                Forms\Components\Select::make('category_id')
                    ->label(__('translations.category'))
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_primary')
                    ->label(__('translations.is_primary'))
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('translations.category_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('translations.description'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('translations.is_visible'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_visible')
                    ->label(__('translations.is_visible'))
                    ->options([
                        true => __('translations.yes'),
                        false => __('translations.no'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('translations.sort_order'))
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_primary')
                            ->label(__('translations.is_primary'))
                            ->default(false),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
