<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserBehaviorResource\Pages;
use BackedEnum;
use App\Models\User;
use App\Models\UserBehavior;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * UserBehaviorResource
 *
 * Filament v4 resource for UserBehavior management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class UserBehaviorResource extends Resource
{
    protected static ?string $model = UserBehavior::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static UnitEnum|string|null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 5;

    private static function behaviorTypeOptions(): array
    {
        return [
            'view' => __('admin.user_behaviors.behavior_types.view'),
            'click' => __('admin.user_behaviors.behavior_types.click'),
            'add_to_cart' => __('admin.user_behaviors.behavior_types.add_to_cart'),
            'remove_from_cart' => __('admin.user_behaviors.behavior_types.remove_from_cart'),
            'purchase' => __('admin.user_behaviors.behavior_types.purchase'),
            'search' => __('admin.user_behaviors.behavior_types.search'),
            'filter' => __('admin.user_behaviors.behavior_types.filter'),
            'sort' => __('admin.user_behaviors.behavior_types.sort'),
            'wishlist' => __('admin.user_behaviors.behavior_types.wishlist'),
            'share' => __('admin.user_behaviors.behavior_types.share'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.user_behaviors.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.user_behaviors.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.user_behaviors.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('admin.user_behaviors.basic_information'))
                    ->description(__('admin.user_behaviors.basic_information_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label(__('admin.user_behaviors.user'))
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                                TextInput::make('session_id')
                                    ->label(__('admin.user_behaviors.session_id'))
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('admin.user_behaviors.product'))
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                                Select::make('category_id')
                                    ->label(__('admin.user_behaviors.category'))
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                            ]),
                        Select::make('behavior_type')
                            ->label(__('admin.user_behaviors.behavior_type'))
                            ->options(self::behaviorTypeOptions())
                            ->required()
                            ->searchable(),
                        DateTimePicker::make('created_at')
                            ->label(__('admin.user_behaviors.created_at'))
                            ->default(now())
                            ->displayFormat('d/m/Y H:i:s'),
                    ])
                    ->columns(1),
                Section::make(__('admin.user_behaviors.technical_details'))
                    ->description(__('admin.user_behaviors.technical_details_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('ip_address')
                                    ->label(__('admin.user_behaviors.ip_address'))
                                    ->maxLength(45)
                                    ->columnSpan(1),
                                TextInput::make('referrer')
                                    ->label(__('admin.user_behaviors.referrer'))
                                    ->maxLength(500)
                                    ->columnSpan(1),
                            ]),
                        Textarea::make('user_agent')
                            ->label(__('admin.user_behaviors.user_agent'))
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                Section::make(__('admin.user_behaviors.metadata'))
                    ->description(__('admin.user_behaviors.metadata_description'))
                    ->schema([
                        KeyValue::make('metadata')
                            ->label(__('admin.user_behaviors.metadata'))
                            ->keyLabel(__('admin.user_behaviors.key'))
                            ->valueLabel(__('admin.user_behaviors.value'))
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin.user_behaviors.user'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('behavior_type')
                    ->label(__('admin.user_behaviors.behavior_type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'view' => 'info',
                        'click' => 'success',
                        'add_to_cart' => 'warning',
                        'remove_from_cart' => 'danger',
                        'purchase' => 'primary',
                        'search' => 'secondary',
                        'filter' => 'gray',
                        'sort' => 'gray',
                        'wishlist' => 'pink',
                        'share' => 'blue',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label(__('admin.user_behaviors.product'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->limit(30),
                TextColumn::make('category.name')
                    ->label(__('admin.user_behaviors.category'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('session_id')
                    ->label(__('admin.user_behaviors.session_id'))
                    ->searchable()
                    ->toggleable()
                    ->limit(20),
                TextColumn::make('referrer')
                    ->label(__('admin.user_behaviors.referrer'))
                    ->limit(30)
                    ->toggleable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('ip_address')
                    ->label(__('admin.user_behaviors.ip_address'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('admin.user_behaviors.created_at'))
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->weight('medium'),
            ])
            ->groups([
                Group::make('behavior_type')
                    ->label(__('admin.user_behaviors.behavior_type'))
                    ->collapsible(),
                Group::make('user.name')
                    ->label(__('admin.user_behaviors.user'))
                    ->collapsible(),
                Group::make('created_at')
                    ->label(__('admin.user_behaviors.created_at'))
                    ->date()
                    ->collapsible(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('admin.user_behaviors.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('behavior_type')
                    ->label(__('admin.user_behaviors.behavior_type'))
                    ->options(self::behaviorTypeOptions())
                    ->multiple(),
                SelectFilter::make('product_id')
                    ->label(__('admin.user_behaviors.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category_id')
                    ->label(__('admin.user_behaviors.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('has_product')
                    ->label(__('admin.user_behaviors.has_product'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('product_id'),
                        false: fn(Builder $query) => $query->whereNull('product_id'),
                        blank: fn(Builder $query) => $query,
                    ),
                TernaryFilter::make('has_category')
                    ->label(__('admin.user_behaviors.has_category'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('category_id'),
                        false: fn(Builder $query) => $query->whereNull('category_id'),
                        blank: fn(Builder $query) => $query,
                    ),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label(__('admin.user_behaviors.created_from')),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label(__('admin.user_behaviors.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('recent_behaviors')
                    ->label(__('admin.user_behaviors.recent_behaviors'))
                    ->query(fn(Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
                Filter::make('today')
                    ->label(__('admin.user_behaviors.today'))
                    ->query(fn(Builder $query): Builder => $query->whereDate('created_at', today())),
                Filter::make('this_week')
                    ->label(__('admin.user_behaviors.this_week'))
                    ->query(fn(Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),
                Filter::make('this_month')
                    ->label(__('admin.user_behaviors.this_month'))
                    ->query(fn(Builder $query): Builder => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('analyze')
                    ->label(__('admin.user_behaviors.analyze'))
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->action(function (UserBehavior $record): void {
                        // Analysis logic here
                        Notification::make()
                            ->title(__('admin.user_behaviors.analysis_completed'))
                            ->success()
                            ->send();
                    }),
                Action::make('view_user_journey')
                    ->label(__('admin.user_behaviors.user_journey'))
                    ->icon('heroicon-o-map')
                    ->color('warning')
                    ->action(function (UserBehavior $record): void {
                        // User journey analysis logic
                        Notification::make()
                            ->title(__('admin.user_behaviors.user_journey_analyzed'))
                            ->success()
                            ->send();
                    }),
                Action::make('view_session_details')
                    ->label(__('admin.user_behaviors.session_analysis'))
                    ->icon('heroicon-o-clock')
                    ->color('secondary')
                    ->action(function (UserBehavior $record): void {
                        // Session analysis logic
                        Notification::make()
                            ->title(__('admin.user_behaviors.session_analyzed'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('export_analytics')
                        ->label(__('admin.user_behaviors.export_analytics'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Export logic here
                            Notification::make()
                                ->title(__('admin.user_behaviors.exported_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('analyze_selected')
                        ->label(__('admin.user_behaviors.analyze_selected'))
                        ->icon('heroicon-o-chart-bar')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            // Bulk analysis logic
                            Notification::make()
                                ->title(__('admin.user_behaviors.bulk_analysis_completed', ['count' => $records->count()]))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('generate_insights')
                        ->label(__('admin.user_behaviors.generate_insights'))
                        ->icon('heroicon-o-light-bulb')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            // Insights generation logic
                            Notification::make()
                                ->title(__('admin.user_behaviors.insights_generated', ['count' => $records->count()]))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('analytics_dashboard')
                    ->label(__('admin.user_behaviors.analytics_dashboard'))
                    ->icon('heroicon-o-chart-pie')
                    ->color('primary')
                    ->url(fn(): string => route('filament.admin.resources.user-behaviors.analytics')),
                Action::make('export_all')
                    ->label(__('admin.user_behaviors.export_all'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (): void {
                        // Export all logic
                        Notification::make()
                            ->title(__('admin.user_behaviors.all_data_exported'))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUserBehaviors::route('/'),
            'create' => Pages\CreateUserBehavior::route('/create'),
            'view' => Pages\ViewUserBehavior::route('/{record}'),
            'edit' => Pages\EditUserBehavior::route('/{record}/edit'),
            'analytics' => Pages\Analytics::route('/analytics'),
        ];
    }
}
