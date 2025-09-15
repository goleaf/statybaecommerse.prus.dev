<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\RelationManagers;

use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $title = 'Categories';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label(__('news.category'))
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500),
                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('news.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                Forms\Components\Toggle::make('is_primary')
                    ->label(__('news.is_primary'))
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('category.name')
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('news.category'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category.description')
                    ->label(__('news.description'))
                    ->searchable()
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('category.slug')
                    ->label(__('news.slug'))
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('news.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 100 => 'danger',
                        $state >= 50 => 'warning',
                        $state >= 20 => 'info',
                        $state >= 10 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label(__('news.is_primary'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('news.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('news.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label(__('news.is_primary'))
                    ->boolean()
                    ->trueLabel(__('news.primary_only'))
                    ->falseLabel(__('news.secondary_only'))
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
