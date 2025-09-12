<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsEventResource\Pages;
use App\Models\AnalyticsEvent;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

final class AnalyticsEventResource extends Resource
{
    protected static ?string $model = AnalyticsEvent::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';


    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make(__('admin.analytics.sections.event_information'))
                    ->components([
                        Forms\Components\TextInput::make('event_type')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('session_id')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('url')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('referrer')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ip_address')
                            ->ip()
                            ->maxLength(45),
                        Forms\Components\TextInput::make('country_code')
                            ->maxLength(2),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->required(),
                    ])
                    ->columns(2),
                \Filament\Schemas\Components\Section::make(__('admin.analytics.sections.event_properties'))
                    ->components([
                        Forms\Components\KeyValue::make('properties')
                            ->keyLabel('Property')
                            ->valueLabel('Value'),
                    ])
                    ->collapsible(),
                \Filament\Schemas\Components\Section::make(__('admin.analytics.sections.user_agent'))
                    ->components([
                        Forms\Components\Textarea::make('user_agent')
                            ->rows(3),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'page_view' => 'info',
                        'product_view' => 'primary',
                        'add_to_cart' => 'success',
                        'purchase' => 'success',
                        'search' => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.users.singular'))
                    ->placeholder(__('admin.analytics.anonymous'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('session_id')
                    ->label(__('admin.analytics.session'))
                    ->limit(10)
                    ->tooltip(fn($record) => $record->session_id),
                Tables\Columns\TextColumn::make('url')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->url),
                Tables\Columns\TextColumn::make('country_code')
                    ->label(__('admin.countries.singular'))
                    ->badge()
                    ->placeholder(__('admin.analytics.unknown')),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->options([
                        'page_view' => __('admin.analytics.event_types.page_view'),
                        'product_view' => __('admin.analytics.event_types.product_view'),
                        'add_to_cart' => __('admin.analytics.event_types.add_to_cart'),
                        'remove_from_cart' => __('admin.analytics.event_types.remove_from_cart'),
                        'purchase' => __('admin.analytics.event_types.purchase'),
                        'search' => __('admin.analytics.event_types.search'),
                        'user_register' => __('admin.analytics.event_types.user_register'),
                        'user_login' => __('admin.analytics.event_types.user_login'),
                    ]),
                Tables\Filters\Filter::make('has_user')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('user_id'))
                    ->label(__('admin.analytics.filters.registered_only')),
                Tables\Filters\Filter::make('anonymous_only')
                    ->query(fn(Builder $query): Builder => $query->whereNull('user_id'))
                    ->label(__('admin.analytics.filters.anonymous_only')),
                Tables\Filters\Filter::make('today')
                    ->query(fn(Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->label(__('admin.date_ranges.today')),
                Tables\Filters\Filter::make('this_week')
                    ->query(fn(Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->label(__('admin.analytics.this_week')),
                Tables\Filters\Filter::make('this_month')
                    ->query(fn(Builder $query): Builder => $query->whereMonth('created_at', now()->month))
                    ->label(__('admin.analytics.this_month')),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\BulkAction::make('cleanup_old_events')
                        ->label('Cleanup Old Events')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function () {
                            AnalyticsEvent::where('created_at', '<', now()->subMonths(3))->delete();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Cleanup Old Analytics Events')
                        ->modalDescription('This will delete all analytics events older than 3 months. This action cannot be undone.')
                        ->modalSubmitActionLabel('Cleanup Old Events'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalyticsEvents::route('/'),
            'view' => Pages\ViewAnalyticsEvent::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['event_type', 'user.name', 'url'];
    }

    public static function canCreate(): bool
    {
        return false;  // created programmatically
    }

    public static function canEdit($record): bool
    {
        return false;  // read-only
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.analytics');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.analytics.events');
    }
}
