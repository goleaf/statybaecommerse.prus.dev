<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsCategoryResource\RelationManagers;

use App\Models\NewsCategory;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

final class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $title = 'Sub Categories';

    protected static ?string $modelLabel = 'Sub Category';

    protected static ?string $pluralModelLabel = 'Sub Categories';

    public function form(Schema $schema): Schema
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(NewsCategory::class, 'slug', ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(9999),
                        Forms\Components\ColorPicker::make('color')
                            ->placeholder('#000000'),
                        Forms\Components\Select::make('icon')
                            ->options([
                                'heroicon-o-tag' => 'Tag',
                                'heroicon-o-document-text' => 'Document',
                                'heroicon-o-newspaper' => 'Newspaper',
                                'heroicon-o-folder' => 'Folder',
                                'heroicon-o-rectangle-stack' => 'Stack',
                                'heroicon-o-squares-2x2' => 'Grid',
                                'heroicon-o-bookmark' => 'Bookmark',
                                'heroicon-o-star' => 'Star',
                                'heroicon-o-fire' => 'Fire',
                                'heroicon-o-bolt' => 'Bolt',
                                'heroicon-o-light-bulb' => 'Light Bulb',
                                'heroicon-o-cog' => 'Settings',
                                'heroicon-o-wrench-screwdriver' => 'Tools',
                                'heroicon-o-building-office' => 'Building',
                                'heroicon-o-home' => 'Home',
                            ])
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('is_visible')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('icon')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('news_count')
                    ->counts('news')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
