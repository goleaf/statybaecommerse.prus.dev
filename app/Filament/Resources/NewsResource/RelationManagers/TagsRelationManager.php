<?php declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class TagsRelationManager extends RelationManager
{
    protected static string $relationship = 'tags';

    public function form(Schema $form): Schema
    {
        return $form->components([
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.news.tags.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->label(__('admin.news.tags.fields.slug'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(__('admin.news.tags.fields.description'))
                    ->maxLength(500)
                    ->rows(3),
                Forms\Components\ColorPicker::make('color')
                    ->label(__('admin.news.tags.fields.color')),
                Forms\Components\Toggle::make('is_visible')
                    ->label(__('admin.news.tags.fields.is_visible'))
                    ->default(true),
                Forms\Components\TextInput::make('sort_order')
                    ->label(__('admin.news.tags.fields.sort_order'))
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label(__('admin.news.tags.fields.color')),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.news.tags.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('admin.news.tags.fields.slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.news.tags.fields.description'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('admin.news.tags.fields.is_visible'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.news.tags.fields.sort_order'))
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('admin.news.tags.filters.is_visible')),
                Tables\Filters\SelectFilter::make('color')
                    ->label(__('admin.news.tags.filters.color'))
                    ->options([
                        '#ef4444' => 'Red',
                        '#f97316' => 'Orange',
                        '#eab308' => 'Yellow',
                        '#22c55e' => 'Green',
                        '#06b6d4' => 'Cyan',
                        '#3b82f6' => 'Blue',
                        '#8b5cf6' => 'Purple',
                        '#ec4899' => 'Pink',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
