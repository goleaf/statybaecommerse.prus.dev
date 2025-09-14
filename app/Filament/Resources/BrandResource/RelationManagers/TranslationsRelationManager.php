<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final /**
 * TranslationsRelationManager
 * 
 * Filament resource for admin panel management.
 */
class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'admin.brands.relations.translations_title';

    protected static ?string $modelLabel = 'admin.brands.relations.translation_label';

    protected static ?string $pluralModelLabel = 'admin.brands.relations.translations_label';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('locale')
                    ->label(__('admin.brands.fields.locale'))
                    ->options([
                        'lt' => 'Lietuvių',
                        'en' => 'English',
                        'de' => 'Deutsch',
                    ])
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.brands.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(__('admin.brands.fields.description'))
                    ->maxLength(1000)
                    ->rows(3),
                Forms\Components\TextInput::make('seo_title')
                    ->label(__('admin.brands.fields.seo_title'))
                    ->maxLength(60),
                Forms\Components\Textarea::make('seo_description')
                    ->label(__('admin.brands.fields.seo_description'))
                    ->maxLength(160)
                    ->rows(3),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('locale')
            ->columns([
                Tables\Columns\TextColumn::make('locale')
                    ->label(__('admin.brands.fields.locale'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lt' => 'primary',
                        'en' => 'success',
                        'de' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lt' => 'Lietuvių',
                        'en' => 'English',
                        'de' => 'Deutsch',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.brands.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.brands.fields.description'))
                    ->limit(100)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('seo_title')
                    ->label(__('admin.brands.fields.seo_title'))
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.brands.fields.created_at'))
                    ->date('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.brands.fields.updated_at'))
                    ->date('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->label(__('admin.brands.filters.translation_locale'))
                    ->options([
                        'lt' => 'Lietuvių',
                        'en' => 'English',
                        'de' => 'Deutsch',
                    ]),
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
            ->defaultSort('locale', 'asc');
    }
}
