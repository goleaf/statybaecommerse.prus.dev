<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralStatisticsResource\Pages;
use App\Models\ReferralStatistics;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

final class ReferralStatisticsResource extends Resource
{
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Referral';
    }

    protected static ?string $model = ReferralStatistics::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-chart-bar-square';
    }

    protected static ?int $navigationSort = 14;

    protected static ?string $recordTitleAttribute = 'date';

    public static function getNavigationLabel(): string
    {
        return __('referral_statistics.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('referral_statistics.plural');
    }

    public static function getModelLabel(): string
    {
        return __('referral_statistics.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                Section::make(__('referral_statistics.sections.basic_info'))
                    ->description(__('referral_statistics.sections.basic_info_description'))
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label(__('referral_statistics.fields.user_id'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('date')
                            ->label(__('referral_statistics.fields.date'))
                            ->required()
                            ->default(now()),
                    ]),
                Section::make(__('referral_statistics.sections.referral_stats'))
                    ->description(__('referral_statistics.sections.referral_stats_description'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('total_referrals')
                            ->label(__('referral_statistics.fields.total_referrals'))
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->step(1),
                        TextInput::make('completed_referrals')
                            ->label(__('referral_statistics.fields.completed_referrals'))
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->step(1),
                        TextInput::make('pending_referrals')
                            ->label(__('referral_statistics.fields.pending_referrals'))
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->step(1),
                    ]),
                Section::make(__('referral_statistics.sections.financial_stats'))
                    ->description(__('referral_statistics.sections.financial_stats_description'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('total_rewards_earned')
                            ->label(__('referral_statistics.fields.total_rewards_earned'))
                            ->numeric()
                            ->default(0.0)
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€'),
                        TextInput::make('total_discounts_given')
                            ->label(__('referral_statistics.fields.total_discounts_given'))
                            ->numeric()
                            ->default(0.0)
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€'),
                    ]),
                Section::make(__('referral_statistics.sections.advanced'))
                    ->description(__('referral_statistics.sections.advanced_description'))
                    ->collapsible()
                    ->schema([
                        KeyValue::make('metadata')
                            ->label(__('referral_statistics.fields.metadata'))
                            ->keyLabel(__('referral_statistics.fields.metadata_key'))
                            ->valueLabel(__('referral_statistics.fields.metadata_value'))
                            ->reorderable()
                            ->addActionLabel(__('referral_statistics.actions.add_metadata'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('referral_statistics.fields.user_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(30),
                TextColumn::make('date')
                    ->label(__('referral_statistics.fields.date'))
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('total_referrals')
                    ->label(__('referral_statistics.fields.total_referrals'))
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('completed_referrals')
                    ->label(__('referral_statistics.fields.completed_referrals'))
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->badge()
                    ->color('success'),
                TextColumn::make('pending_referrals')
                    ->label(__('referral_statistics.fields.pending_referrals'))
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('total_rewards_earned')
                    ->label(__('referral_statistics.fields.total_rewards_earned'))
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->weight('medium'),
                TextColumn::make('total_discounts_given')
                    ->label(__('referral_statistics.fields.total_discounts_given'))
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->weight('medium'),
                TextColumn::make('created_at')
                    ->label(__('referral_statistics.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('referral_statistics.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('referral_statistics.filters.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('date_range')
                    ->label(__('referral_statistics.filters.date_range'))
                    ->form([
                        DatePicker::make('from')
                            ->label(__('referral_statistics.filters.from_date')),
                        DatePicker::make('until')
                            ->label(__('referral_statistics.filters.until_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                Filter::make('has_referrals')
                    ->label(__('referral_statistics.filters.has_referrals'))
                    ->query(fn(Builder $query): Builder => $query->where('total_referrals', '>', 0)),
                Filter::make('has_rewards')
                    ->label(__('referral_statistics.filters.has_rewards'))
                    ->query(fn(Builder $query): Builder => $query->where('total_rewards_earned', '>', 0)),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('refresh_stats')
                    ->label(__('referral_statistics.actions.refresh_stats'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (ReferralStatistics $record): void {
                        Notification::make()
                            ->title(__('referral_statistics.notifications.stats_refreshed'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('refresh_all_stats')
                        ->label(__('referral_statistics.actions.refresh_all_stats'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            Notification::make()
                                ->title(__('referral_statistics.notifications.all_stats_refreshed'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('referral_statistics.sections.basic_info'))
                    ->schema([
                        TextEntry::make('user.name')
                            ->label(__('referral_statistics.fields.user_name'))
                            ->weight('medium'),
                        TextEntry::make('date')
                            ->label(__('referral_statistics.fields.date'))
                            ->date()
                            ->badge()
                            ->color('info'),
                    ])
                    ->columns(2),
                Section::make(__('referral_statistics.sections.referral_stats'))
                    ->schema([
                        TextEntry::make('total_referrals')
                            ->label(__('referral_statistics.fields.total_referrals'))
                            ->numeric()
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('completed_referrals')
                            ->label(__('referral_statistics.fields.completed_referrals'))
                            ->numeric()
                            ->badge()
                            ->color('success'),
                        TextEntry::make('pending_referrals')
                            ->label(__('referral_statistics.fields.pending_referrals'))
                            ->numeric()
                            ->badge()
                            ->color('warning'),
                    ])
                    ->columns(3),
                Section::make(__('referral_statistics.sections.financial_stats'))
                    ->schema([
                        TextEntry::make('total_rewards_earned')
                            ->label(__('referral_statistics.fields.total_rewards_earned'))
                            ->money('EUR')
                            ->weight('medium'),
                        TextEntry::make('total_discounts_given')
                            ->label(__('referral_statistics.fields.total_discounts_given'))
                            ->money('EUR')
                            ->weight('medium'),
                    ])
                    ->columns(2),
                Section::make(__('referral_statistics.sections.advanced'))
                    ->collapsible()
                    ->schema([
                        TextEntry::make('metadata')
                            ->label(__('referral_statistics.fields.metadata'))
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) {
                                    return null;
                                }
                                return json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            })
                            ->placeholder(__('referral_statistics.placeholders.no_metadata')),
                    ]),
                Section::make(__('referral_statistics.sections.timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('referral_statistics.fields.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('referral_statistics.fields.updated_at'))
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
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
            'index' => Pages\ListReferralStatistics::route('/'),
            'create' => Pages\CreateReferralStatistics::route('/create'),
            'view' => Pages\ViewReferralStatistics::route('/{record}'),
            'edit' => Pages\EditReferralStatistics::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['date', 'metadata'];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) self::$model::count();
    }
}
