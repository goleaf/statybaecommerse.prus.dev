<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class TranslationsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Collection Translations';

    public function form(Form $form): Form
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('locale')
                    ->label(__('admin.collections.fields.locale'))
                    ->options([
                        'lt' => 'Lithuanian',
                        'en' => 'English',
                        'de' => 'German',
                        'ru' => 'Russian',
                    ])
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.collections.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('admin.collections.placeholders.name')),
                Forms\Components\Textarea::make('description')
                    ->label(__('admin.collections.fields.description'))
                    ->rows(3)
                    ->maxLength(1000)
                    ->placeholder(__('admin.collections.placeholders.description')),
                Forms\Components\TextInput::make('seo_title')
                    ->label(__('admin.collections.fields.seo_title'))
                    ->maxLength(255)
                    ->placeholder(__('admin.collections.placeholders.seo_title')),
                Forms\Components\Textarea::make('seo_description')
                    ->label(__('admin.collections.fields.seo_description'))
                    ->rows(2)
                    ->maxLength(500)
                    ->placeholder(__('admin.collections.placeholders.seo_description')),
            ]);
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('locale')
                    ->label(__('admin.collections.fields.locale'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lt'    => 'success',
                        'en'    => 'primary',
                        'de'    => 'warning',
                        'ru'    => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lt'    => '🇱🇹 Lithuanian',
                        'en'    => '🇬🇧 English',
                        'de'    => '🇩🇪 German',
                        'ru'    => '🇷🇺 Russian',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.collections.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.collections.fields.description'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),
                Tables\Columns\TextColumn::make('seo_title')
                    ->label(__('admin.collections.fields.seo_title'))
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }

                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('seo_description')
                    ->label(__('admin.collections.fields.seo_description'))
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }

                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.collections.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.collections.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->label(__('admin.collections.filters.locale'))
                    ->options([
                        'lt' => '🇱🇹 Lithuanian',
                        'en' => '🇬🇧 English',
                        'de' => '🇩🇪 German',
                        'ru' => '🇷🇺 Russian',
                    ])
                    ->searchable(),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.collections.actions.add_translation')),
            ])
            ->actions([
                EditAction::make()
                    ->label(__('admin.collections.actions.edit_translation')),
                DeleteAction::make()
                    ->label(__('admin.collections.actions.delete_translation'))
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.collections.confirmations.delete_translation')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('admin.collections.actions.delete_translations'))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.collections.confirmations.delete_translations')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}