<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsEventResource\Pages;
use App\Models\AnalyticsEvent;
use BackedEnum;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final /**
 * AnalyticsEventResource
 * 
 * Filament resource for admin panel management.
 */
class AnalyticsEventResource extends Resource
{
    protected static ?string $model = AnalyticsEvent::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.analytics.sections.event_information'))
                    ->schema([
                        Forms\Components\Select::make('event_type')
                            ->label(__('admin.analytics.event_type'))
                            ->required()
                            ->options(AnalyticsEvent::getEventTypes())
                            ->searchable()
                            ->live(),
                        Forms\Components\TextInput::make('session_id')
                            ->label(__('admin.analytics.session_id'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('user_id')
                            ->label(__('admin.users.singular'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('url')
                            ->label(__('admin.analytics.url'))
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('referrer')
                            ->label(__('admin.analytics.referrer'))
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label(__('admin.analytics.ip_address'))
                            ->ip()
                            ->maxLength(45)
                            ->nullable(),
                        Forms\Components\TextInput::make('country_code')
                            ->label(__('admin.countries.singular'))
                            ->maxLength(2)
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label(__('admin.analytics.created_at'))
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('admin.analytics.sections.device_information'))
                    ->schema([
                        Forms\Components\Select::make('device_type')
                            ->label(__('admin.analytics.device_type'))
                            ->options(AnalyticsEvent::getDeviceTypes())
                            ->nullable(),
                        Forms\Components\Select::make('browser')
                            ->label(__('admin.analytics.browser'))
                            ->options(AnalyticsEvent::getBrowsers())
                            ->nullable(),
                        Forms\Components\TextInput::make('os')
                            ->label(__('admin.analytics.os'))
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('screen_resolution')
                            ->label(__('admin.analytics.screen_resolution'))
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('admin.analytics.sections.trackable_information'))
                    ->schema([
                        Forms\Components\TextInput::make('trackable_type')
                            ->label(__('admin.analytics.trackable_type'))
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('trackable_id')
                            ->label(__('admin.analytics.trackable_id'))
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('value')
                            ->label(__('admin.analytics.value'))
                            ->numeric()
                            ->step(0.01)
                            ->nullable(),
                        Forms\Components\TextInput::make('currency')
                            ->label(__('admin.analytics.currency'))
                            ->maxLength(3)
                            ->default('EUR')
                            ->nullable(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('admin.analytics.sections.event_properties'))
                    ->schema([
                        Forms\Components\KeyValue::make('properties')
                            ->keyLabel(__('admin.analytics.property'))
                            ->valueLabel(__('admin.analytics.value'))
                            ->nullable(),
                    ])
                    ->collapsible(),
                Forms\Components\Section::make(__('admin.analytics.sections.user_agent'))
                    ->schema([
                        Forms\Components\Textarea::make('user_agent')
                            ->label(__('admin.analytics.user_agent'))
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_type')
                    ->label(__('admin.analytics.event_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'page_view' => 'info',
                        'product_view' => 'primary',
                        'add_to_cart' => 'success',
                        'purchase' => 'success',
                        'search' => 'warning',
                        'user_register' => 'success',
                        'user_login' => 'info',
                        'user_logout' => 'gray',
                        'newsletter_signup' => 'warning',
                        'contact_form' => 'primary',
                        'download' => 'secondary',
                        'video_play' => 'danger',
                        'social_share' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (AnalyticsEvent $record) => $record->getEventTypeLabelAttribute())
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
                    ->tooltip(fn ($record) => $record->session_id)
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->label(__('admin.analytics.url'))
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->url)
                    ->searchable(),
                Tables\Columns\TextColumn::make('device_type')
                    ->label(__('admin.analytics.device_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'desktop' => 'primary',
                        'mobile' => 'success',
                        'tablet' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (AnalyticsEvent $record) => $record->getDeviceIconAttribute())
                    ->sortable(),
                Tables\Columns\TextColumn::make('browser')
                    ->label(__('admin.analytics.browser'))
                    ->badge()
                    ->color('secondary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('country_code')
                    ->label(__('admin.countries.singular'))
                    ->badge()
                    ->placeholder(__('admin.analytics.unknown')),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('admin.analytics.value'))
                    ->formatStateUsing(fn (AnalyticsEvent $record) => $record->getFormattedValueAttribute())
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.analytics.created_at'))
                    ->date('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->label(__('admin.analytics.event_type'))
                    ->options(AnalyticsEvent::getEventTypes()),
                Tables\Filters\SelectFilter::make('device_type')
                    ->label(__('admin.analytics.device_type'))
                    ->options(AnalyticsEvent::getDeviceTypes()),
                Tables\Filters\SelectFilter::make('browser')
                    ->label(__('admin.analytics.browser'))
                    ->options(AnalyticsEvent::getBrowsers()),
                Tables\Filters\Filter::make('has_user')
                    ->label(__('admin.analytics.filters.registered_only'))
                    ->query(fn (Builder $query): Builder => $query->registeredUsers()),
                Tables\Filters\Filter::make('anonymous_only')
                    ->label(__('admin.analytics.filters.anonymous_only'))
                    ->query(fn (Builder $query): Builder => $query->anonymousUsers()),
                Tables\Filters\Filter::make('with_value')
                    ->label(__('admin.analytics.filters.with_value'))
                    ->query(fn (Builder $query): Builder => $query->withValue()),
                Tables\Filters\Filter::make('today')
                    ->label(__('admin.date_ranges.today'))
                    ->query(fn (Builder $query): Builder => $query->today()),
                Tables\Filters\Filter::make('this_week')
                    ->label(__('admin.analytics.this_week'))
                    ->query(fn (Builder $query): Builder => $query->thisWeek()),
                Tables\Filters\Filter::make('this_month')
                    ->label(__('admin.analytics.this_month'))
                    ->query(fn (Builder $query): Builder => $query->thisMonth()),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('admin.users.singular'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('session_id')
                    ->label(__('admin.analytics.session'))
                    ->form([
                        Forms\Components\TextInput::make('session_id')
                            ->label(__('admin.analytics.session'))
                            ->placeholder(__('admin.analytics.enter_session_id')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['session_id'],
                            fn (Builder $query, $sessionId): Builder => $query->where('session_id', 'like', "%{$sessionId}%"),
                        );
                    }),
                Tables\Filters\Filter::make('created_at')
                    ->label(__('admin.date_ranges.date_range'))
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('admin.analytics.from')),
                        Forms\Components\DatePicker::make('until')
                            ->label(__('admin.analytics.until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label(__('admin.actions.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        // Export logic would go here
                        return redirect()->back();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('cleanup_old_events')
                        ->label(__('admin.analytics.cleanup_old_events'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function () {
                            AnalyticsEvent::where('created_at', '<', now()->subMonths(3))->delete();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.analytics.cleanup_old_analytics_events'))
                        ->modalDescription(__('admin.analytics.cleanup_description'))
                        ->modalSubmitActionLabel(__('admin.analytics.cleanup_old_events')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalyticsEvents::route('/'),
            'create' => Pages\CreateAnalyticsEvent::route('/create'),
            'view' => Pages\ViewAnalyticsEvent::route('/{record}'),
            'edit' => Pages\EditAnalyticsEvent::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['event_type', 'user.name', 'url', 'session_id'];
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        return true;
    }

    public static function canDelete($record): bool
    {
        return true;
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
