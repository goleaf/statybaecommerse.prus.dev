<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\RelationManagers;

use App\Models\NewsCategory;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

final class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $title = 'Categories';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('id')
                    ->label(__('news.fields.category'))
                    ->relationship('news_categories', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label(__('news.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('news.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(NewsCategory::class, 'slug'),
                        Forms\Components\Textarea::make('description')
                            ->label(__('news.fields.description'))
                            ->maxLength(500)
                            ->rows(3),
                        Forms\Components\Select::make('parent_id')
                            ->label(__('news.fields.parent_category'))
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Toggle::make('is_visible')
                            ->label(__('news.fields.is_visible'))
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('news.fields.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\ColorPicker::make('color')
                            ->label(__('news.fields.color')),
                        Forms\Components\TextInput::make('icon')
                            ->label(__('news.fields.icon'))
                            ->maxLength(255),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('news.fields.category'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('news.fields.description'))
                    ->searchable()
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('news.fields.slug'))
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('news.fields.parent_category'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder(__('news.fields.no_parent'))
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('news.fields.is_visible'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('news.fields.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 100 => 'danger',
                        $state >= 50 => 'warning',
                        $state >= 20 => 'info',
                        $state >= 10 => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('color')
                    ->label(__('news.fields.color'))
                    ->badge()
                    ->color(fn (string $state): string => $state ?? 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('icon')
                    ->label(__('news.fields.icon'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('news.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label(__('news.fields.parent_category'))
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('news.fields.is_visible'))
                    ->boolean()
                    ->trueLabel(__('news.visible_only'))
                    ->falseLabel(__('news.hidden_only'))
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
