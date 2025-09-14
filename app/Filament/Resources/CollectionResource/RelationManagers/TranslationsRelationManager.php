<?php declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use App\Models\Translations\CollectionTranslation;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Collection Translations';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('locale')
                    ->label(__('admin.collections.fields.locale'))
                    ->options([
                        'lt' => 'Lithuanian',
                        'en' => 'English',
                        'de' => 'German',
                        'ru' => 'Russian',
                    ])
                    ->required(),
                    ->searchable(),

                Forms\Components\TextInput::make('name')
                    ->label(__('admin.collections.fields.name'))
                    ->required(),
                    ->maxLength(255),
                    ->placeholder(__('admin.collections.placeholders.name')),

                Forms\Components\Textarea::make('description')
                    ->label(__('admin.collections.fields.description'))
                    ->rows(3),
                    ->maxLength(1000)
                    ->placeholder(__('admin.collections.placeholders.description')),

                Forms\Components\TextInput::make('seo_title')
                    ->label(__('admin.collections.fields.seo_title'))
                    ->maxLength(255),
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
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('locale')
                    ->label(__('admin.collections.fields.locale'))
                    ->badge(),
                    ->color(fn(string $state): string => match ($state) {
                        'lt' => 'success',
                        'en' => 'primary',
                        'de' => 'warning',
                        'ru' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'lt' => '🇱🇹 Lithuanian',
                        'en' => '🇬🇧 English',
                        'de' => '🇩🇪 German',
                        'ru' => '🇷🇺 Russian',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.collections.fields.name'))
                    ->searchable(),
                    ->sortable(),
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.collections.fields.description'))
                    ->limit(50),
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
                    ->dateTime(),
                    ->sortable(),
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.collections.fields.updated_at'))
                    ->dateTime(),
                    ->sortable(),
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
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.collections.actions.add_translation')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('admin.collections.actions.edit_translation')),

                Tables\Actions\DeleteAction::make()
                    ->label(__('admin.collections.actions.delete_translation'))
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.collections.confirmations.delete_translation')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('admin.collections.actions.delete_translations'))
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.collections.confirmations.delete_translations')),
                ]),
            ])
            ->defaultSort("created_at", "desc");
    }
}
    }
}
