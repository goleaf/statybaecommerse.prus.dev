<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserPreferenceResource\Pages;
use App\Models\UserPreference;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * UserPreferenceResource
 *
 * Filament v4 resource for UserPreference management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class UserPreferenceResource extends Resource
{
    protected static ?string $model = UserPreference::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static UnitEnum|string|null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return __('admin.user_preferences.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.user_preferences.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.user_preferences.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('user_id')
                    ->label(__('admin.user_preferences.user'))
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('preference_type')
                    ->label(__('admin.user_preferences.preference_type'))
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
                TextInput::make('preference_key')
                    ->label(__('admin.user_preferences.preference_key'))
                    ->maxLength(255),
                TextInput::make('preference_score')
                    ->label(__('admin.user_preferences.preference_score'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1)
                    ->step(0.000001)
                    ->default(0),
                DateTimePicker::make('last_updated')
                    ->label(__('admin.user_preferences.last_updated'))
                    ->default(now()),
                KeyValue::make('metadata')
                    ->label(__('admin.user_preferences.metadata'))
                    ->keyLabel(__('admin.user_preferences.key'))
                    ->valueLabel(__('admin.user_preferences.value'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin.user_preferences.user'))
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('preference_type')
                    ->label(__('admin.user_preferences.preference_type'))
                    ->colors([
                        'primary' => 'category',
                        'success' => 'brand',
                        'warning' => 'price_range',
                        'info' => 'color',
                        'secondary' => 'size',
                        'danger' => 'material',
                        'gray' => 'style',
                        'pink' => 'feature',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->sortable(),
                TextColumn::make('preference_key')
                    ->label(__('admin.user_preferences.preference_key'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('preference_score')
                    ->label(__('admin.user_preferences.preference_score'))
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('last_updated')
                    ->label(__('admin.user_preferences.last_updated'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.user_preferences.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('admin.user_preferences.user'))
                    ->relationship('user', 'name')
                    ->searchable(),
                SelectFilter::make('preference_type')
                    ->label(__('admin.user_preferences.preference_type'))
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
                Filter::make('score_range')
                    ->form([
                        TextInput::make('min_score')
                            ->label(__('admin.user_preferences.min_score'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1),
                        TextInput::make('max_score')
                            ->label(__('admin.user_preferences.max_score'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_score'] ?? null,
                                fn (Builder $query, $score): Builder => $query->where('preference_score', '>=', $score),
                            )
                            ->when(
                                $data['max_score'] ?? null,
                                fn (Builder $query, $score): Builder => $query->where('preference_score', '<=', $score),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('reset_preference')
                    ->label(__('admin.user_preferences.reset_preference'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (\App\Models\UserPreference $record): void {
                        $record->update(['preference_score' => 0]);
                        Notification::make()
                            ->title(__('admin.user_preferences.preference_reset_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('reset_preferences')
                        ->label(__('admin.user_preferences.reset_preferences'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['preference_score' => 0]);
                            Notification::make()
                                ->title(__('admin.user_preferences.preferences_reset_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('preference_score', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserPreferences::route('/'),
            'create' => Pages\CreateUserPreference::route('/create'),
            'view' => Pages\ViewUserPreference::route('/{record}'),
            'edit' => Pages\EditUserPreference::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return UserPreference::query()->withoutGlobalScopes();
    }
}
