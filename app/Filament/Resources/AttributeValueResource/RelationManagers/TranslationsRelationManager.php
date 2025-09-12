<?php declare(strict_types=1);

namespace App\Filament\Resources\AttributeValueResource\RelationManagers;

use App\Models\AttributeValue;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Translations';

    protected static ?string $modelLabel = 'Translation';

    protected static ?string $pluralModelLabel = 'Translations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('locale')
                    ->label(__('translations.locale'))
                    ->options([
                        'lt' => __('translations.lithuanian'),
                        'en' => __('translations.english'),
                        'de' => __('translations.german'),
                    ])
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('value')
                    ->label(__('attributes.value'))
                    ->maxLength(255)
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label(__('attributes.description'))
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('meta_data')
                    ->label(__('attributes.meta_data'))
                    ->keyLabel(__('attributes.meta_key'))
                    ->valueLabel(__('attributes.meta_value'))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('locale')
            ->columns([
                Tables\Columns\TextColumn::make('locale')
                    ->label(__('translations.locale'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'lt' => 'success',
                        'en' => 'info',
                        'de' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'lt' => __('translations.lithuanian'),
                        'en' => __('translations.english'),
                        'de' => __('translations.german'),
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('attributes.value'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('attributes.description'))
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('meta_data')
                    ->label(__('attributes.meta_data'))
                    ->formatStateUsing(fn($state) => is_array($state) ? count($state) . ' items' : '0 items')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('translations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->label(__('translations.locale'))
                    ->options([
                        'lt' => __('translations.lithuanian'),
                        'en' => __('translations.english'),
                        'de' => __('translations.german'),
                    ]),
                Tables\Filters\Filter::make('with_value')
                    ->label(__('attributes.with_value'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('value')),
                Tables\Filters\Filter::make('with_description')
                    ->label(__('attributes.with_description'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('description')),
                Tables\Filters\Filter::make('with_meta_data')
                    ->label(__('attributes.with_meta_data'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('meta_data')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
