<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserPreferenceResource\Pages;
use App\Models\UserPreference;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class UserPreferenceResource extends Resource
{
    protected static ?string $model = UserPreference::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('preference_type')
                    ->options([
                        'category' => 'Category',
                        'brand' => 'Brand',
                        'price_range' => 'Price Range',
                        'color' => 'Color',
                        'size' => 'Size',
                        'material' => 'Material',
                        'style' => 'Style',
                        'feature' => 'Feature',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('preference_key')
                    ->label('Preference Key')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('preference_score')
                    ->label('Preference Score')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(1)
                    ->step(0.000001),

                Forms\Components\DateTimePicker::make('last_updated')
                    ->label('Last Updated')
                    ->required(),

                Forms\Components\KeyValue::make('metadata')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('preference_type')
                    ->colors([
                        'primary' => 'category',
                        'success' => 'brand',
                        'warning' => 'price_range',
                        'info' => 'color',
                        'danger' => 'size',
                        'secondary' => 'material',
                    ]),

                Tables\Columns\TextColumn::make('preference_key')
                    ->label('Preference Key')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('preference_score')
                    ->label('Score')
                    ->formatStateUsing(fn ($state) => number_format($state, 4))
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_updated')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('preference_type')
                    ->options([
                        'category' => 'Category',
                        'brand' => 'Brand',
                        'price_range' => 'Price Range',
                        'color' => 'Color',
                        'size' => 'Size',
                        'material' => 'Material',
                        'style' => 'Style',
                        'feature' => 'Feature',
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('score_range')
                    ->form([
                        Forms\Components\TextInput::make('min_score')
                            ->label('Min Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.000001),
                        Forms\Components\TextInput::make('max_score')
                            ->label('Max Score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.000001),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['min_score'],
                                fn ($query, $score) => $query->where('preference_score', '>=', $score),
                            )
                            ->when(
                                $data['max_score'],
                                fn ($query, $score) => $query->where('preference_score', '<=', $score),
                            );
                    }),
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
            ->defaultSort('preference_score', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserPreferences::route('/'),
            'create' => Pages\CreateUserPreference::route('/create'),
            'edit' => Pages\EditUserPreference::route('/{record}/edit'),
        ];
    }
}
