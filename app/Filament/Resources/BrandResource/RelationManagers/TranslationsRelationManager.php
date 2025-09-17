<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Translations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('locale')
                    ->label(__('brands.locale'))
                    ->options([
                        'lt' => 'Lithuanian',
                        'en' => 'English',
                        'de' => 'German',
                        'fr' => 'French',
                        'es' => 'Spanish',
                        'it' => 'Italian',
                        'pl' => 'Polish',
                        'ru' => 'Russian',
                    ])
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('name')
                    ->label(__('brands.name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label(__('brands.description'))
                    ->maxLength(1000)
                    ->rows(3),

                Forms\Components\TextInput::make('meta_title')
                    ->label(__('brands.meta_title'))
                    ->maxLength(255),

                Forms\Components\Textarea::make('meta_description')
                    ->label(__('brands.meta_description'))
                    ->maxLength(500)
                    ->rows(2),

                Forms\Components\TextInput::make('meta_keywords')
                    ->label(__('brands.meta_keywords'))
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_active')
                    ->label(__('brands.is_active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('locale')
                    ->label(__('brands.locale'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lt' => 'success',
                        'en' => 'primary',
                        'de' => 'warning',
                        'fr' => 'info',
                        'es' => 'danger',
                        'it' => 'secondary',
                        'pl' => 'gray',
                        'ru' => 'slate',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('brands.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('brands.description'))
                    ->searchable()
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('meta_title')
                    ->label(__('brands.meta_title'))
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('meta_description')
                    ->label(__('brands.meta_description'))
                    ->searchable()
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('meta_keywords')
                    ->label(__('brands.meta_keywords'))
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('brands.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('brands.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('brands.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->label(__('brands.locale'))
                    ->options([
                        'lt' => 'Lithuanian',
                        'en' => 'English',
                        'de' => 'German',
                        'fr' => 'French',
                        'es' => 'Spanish',
                        'it' => 'Italian',
                        'pl' => 'Polish',
                        'ru' => 'Russian',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('brands.is_active'))
                    ->boolean()
                    ->trueLabel(__('brands.active_only'))
                    ->falseLabel(__('brands.inactive_only'))
                    ->native(false),
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
            ->defaultSort('locale');
    }
}

