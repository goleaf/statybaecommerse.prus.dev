<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsEventResource\Pages;
use App\Models\AnalyticsEvent;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class AnalyticsEventResource extends Resource
{
    protected static ?string $model = AnalyticsEvent::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Analytics';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('analytics_events.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('analytics_events.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('analytics_events.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('event_name')
                                ->label(__('analytics_events.event_name'))
                                ->required()
                                ->maxLength(255)
                                ->helperText(__('analytics_events.event_name_help')),
                            Select::make('event_type')
                                ->label(__('analytics_events.event_type'))
                                ->options([
                                    'page_view' => __('analytics_events.types.page_view'),
                                    'click' => __('analytics_events.types.click'),
                                    'form_submit' => __('analytics_events.types.form_submit'),
                                    'purchase' => __('analytics_events.types.purchase'),
                                    'signup' => __('analytics_events.types.signup'),
                                    'login' => __('analytics_events.types.login'),
                                    'logout' => __('analytics_events.types.logout'),
                                    'search' => __('analytics_events.types.search'),
                                    'download' => __('analytics_events.types.download'),
                                    'custom' => __('analytics_events.types.custom'),
                                    // Extended set to avoid validation issues on edit
                                    'product_view' => __('analytics_events.types.product_view'),
                                    'add_to_cart' => __('analytics_events.types.add_to_cart'),
                                    'remove_from_cart' => __('analytics_events.types.remove_from_cart'),
                                    'user_register' => __('analytics_events.types.user_register'),
                                    'user_login' => __('analytics_events.types.user_login'),
                                    'user_logout' => __('analytics_events.types.user_logout'),
                                    'newsletter_signup' => __('analytics_events.types.newsletter_signup'),
                                    'contact_form' => __('analytics_events.types.contact_form'),
                                    'video_play' => __('analytics_events.types.video_play'),
                                    'social_share' => __('analytics_events.types.social_share'),
                                ])
                                ->default('custom'),
                        ]),
                    Textarea::make('description')
                        ->label(__('analytics_events.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->helperText(__('analytics_events.description_help'))
                        ->columnSpanFull(),
                ]),
            Section::make(__('analytics_events.user_information'))
                ->schema([
                    Select::make('user_id')
                        ->label(__('analytics_events.user'))
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Set $set) {
                            if ($state) {
                                $user = User::find($state);
                                if ($user) {
                                    $set('user_name', $user->name);
                                    $set('user_email', $user->email);
                                }
                            }
                        }),
                    TextInput::make('user_name')
                        ->label(__('analytics_events.user_name'))
                        ->disabled(),
                    TextInput::make('session_id')
                        ->label(__('analytics_events.session_id'))
                        ->maxLength(100)
                        ->helperText(__('analytics_events.session_id_help')),
                    TextInput::make('user_agent')
                        ->label(__('analytics_events.user_agent'))
                        ->maxLength(500)
                        ->helperText(__('analytics_events.user_agent_help')),
                ]),
            Section::make(__('analytics_events.event_data'))
                ->schema([
                    KeyValue::make('event_data')
                        ->label(__('analytics_events.event_data'))
                        ->keyLabel(__('analytics_events.event_data_key'))
                        ->valueLabel(__('analytics_events.event_data_value'))
                        ->addActionLabel(__('analytics_events.add_event_data_field')),
                ]),
            Section::make(__('analytics_events.context_information'))
                ->schema([
                    TextInput::make('ip_address')
                        ->label(__('analytics_events.ip_address'))
                        ->maxLength(45)
                        ->rules(['ip'])
                        ->helperText(__('analytics_events.ip_address_help')),
                    TextInput::make('device_type')
                        ->label(__('analytics_events.device_type'))
                        ->maxLength(50)
                        ->helperText(__('analytics_events.device_type_help')),
                    TextInput::make('browser')
                        ->label(__('analytics_events.browser'))
                        ->helperText(__('analytics_events.browser_help')),
                    TextInput::make('os')
                        ->label(__('analytics_events.os'))
                        ->helperText(__('analytics_events.os_help')),
                    TextInput::make('country')
                        ->label(__('analytics_events.country'))
                        ->helperText(__('analytics_events.country_help')),
                    TextInput::make('city')
                        ->label(__('analytics_events.city'))
                        ->helperText(__('analytics_events.city_help')),
                ]),
            Section::make(__('analytics_events.referral_information'))
                ->schema([
                    TextInput::make('referrer_url')
                        ->label(__('analytics_events.referrer_url'))
                        ->url()
                        ->helperText(__('analytics_events.referrer_url_help')),
                    TextInput::make('utm_source')
                        ->label(__('analytics_events.utm_source'))
                        ->helperText(__('analytics_events.utm_source_help')),
                    TextInput::make('utm_medium')
                        ->label(__('analytics_events.utm_medium'))
                        ->helperText(__('analytics_events.utm_medium_help')),
                    TextInput::make('utm_campaign')
                        ->label(__('analytics_events.utm_campaign'))
                        ->helperText(__('analytics_events.utm_campaign_help')),
                    TextInput::make('utm_term')
                        ->label(__('analytics_events.utm_term'))
                        ->helperText(__('analytics_events.utm_term_help')),
                    TextInput::make('utm_content')
                        ->label(__('analytics_events.utm_content'))
                        ->helperText(__('analytics_events.utm_content_help')),
                ]),
            Section::make(__('analytics_events.settings'))
                ->schema([
                    Toggle::make('is_important')
                        ->label(__('analytics_events.is_important'))
                        ->default(false),
                    Toggle::make('is_conversion')
                        ->label(__('analytics_events.is_conversion')),
                    TextInput::make('conversion_value')
                        ->label(__('analytics_events.conversion_value'))
                        ->numeric()
                        ->prefix('€')
                        ->step(0.01)
                        ->minValue(0)
                        ->helperText(__('analytics_events.conversion_value_help')),
                    TextInput::make('conversion_currency')
                        ->label(__('analytics_events.conversion_currency'))
                        ->maxLength(3)
                        ->default('EUR')
                        ->rules(['alpha']),
                    Textarea::make('notes')
                        ->label(__('analytics_events.notes')),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_name')
                    ->label(__('analytics_events.event_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(100),
                TextColumn::make('event_type')
                    ->label(__('analytics_events.event_type'))
                    ->formatStateUsing(fn (string $state): string => __("analytics_events.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'page_view' => 'blue',
                        'click' => 'green',
                        'form_submit' => 'purple',
                        'purchase' => 'orange',
                        'signup' => 'pink',
                        'login' => 'indigo',
                        'logout' => 'gray',
                        'search' => 'teal',
                        'download' => 'yellow',
                        'custom' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('user.name')
                    ->label(__('analytics_events.user'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('session_id')
                    ->label(__('analytics_events.session_id'))
                    ->copyable(),
                TextColumn::make('ip_address')
                    ->label(__('analytics_events.ip_address'))
                    ->color('gray'),
                TextColumn::make('device_type')
                    ->label(__('analytics_events.device_type'))
                    ->color('purple'),
                TextColumn::make('browser')
                    ->label(__('analytics_events.browser'))
                    ->limit(30),
                TextColumn::make('os')
                    ->label(__('analytics_events.os')),
                TextColumn::make('country')
                    ->label(__('analytics_events.country'))
                    ->color('green'),
                TextColumn::make('city')
                    ->label(__('analytics_events.city'))
                    ->color('blue'),
                TextColumn::make('utm_source')
                    ->label(__('analytics_events.utm_source'))
                    ->color('orange'),
                TextColumn::make('utm_medium')
                    ->label(__('analytics_events.utm_medium'))
                    ->color('teal'),
                TextColumn::make('utm_campaign')
                    ->label(__('analytics_events.utm_campaign'))
                    ->color('indigo'),
                IconColumn::make('is_important')
                    ->label(__('analytics_events.is_important'))
                    ->boolean(),
                IconColumn::make('is_conversion')
                    ->label(__('analytics_events.is_conversion'))
                    ->sortable(),
                TextColumn::make('conversion_value')
                    ->label(__('analytics_events.conversion_value'))
                    ->money('EUR')
                    ->alignCenter(),
                TextColumn::make('conversion_currency')
                    ->label(__('analytics_events.conversion_currency')),
                TextColumn::make('created_at')
                    ->label(__('analytics_events.created_at'))
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->label(__('analytics_events.updated_at')),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->options([
                        'page_view' => __('analytics_events.types.page_view'),
                        'click' => __('analytics_events.types.click'),
                        'form_submit' => __('analytics_events.types.form_submit'),
                        'purchase' => __('analytics_events.types.purchase'),
                        'signup' => __('analytics_events.types.signup'),
                        'login' => __('analytics_events.types.login'),
                        'logout' => __('analytics_events.types.logout'),
                        'search' => __('analytics_events.types.search'),
                        'download' => __('analytics_events.types.download'),
                        'custom' => __('analytics_events.types.custom'),
                    ]),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->preload(),
                TernaryFilter::make('is_important')
                    ->trueLabel(__('analytics_events.important_only'))
                    ->falseLabel(__('analytics_events.non_important_only'))
                    ->native(false),
                TernaryFilter::make('is_conversion')
                    ->trueLabel(__('analytics_events.conversions_only'))
                    ->falseLabel(__('analytics_events.non_conversions_only')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('mark_important')
                    ->label(__('analytics_events.mark_important'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (AnalyticsEvent $record): bool => ! $record->is_important)
                    ->action(function (AnalyticsEvent $record): void {
                        $record->update(['is_important' => true]);

                        Notification::make()
                            ->title(__('analytics_events.marked_as_important_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('unmark_important')
                    ->label(__('analytics_events.unmark_important'))
                    ->visible(fn (AnalyticsEvent $record): bool => $record->is_important)
                    ->action(function (AnalyticsEvent $record): void {
                        $record->update(['is_important' => false]);

                        Notification::make()
                            ->title(__('analytics_events.unmarked_as_important_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('mark_conversion')
                    ->label(__('analytics_events.mark_conversion'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (AnalyticsEvent $record): bool => ! $record->is_conversion)
                    ->action(function (AnalyticsEvent $record): void {
                        $record->update(['is_conversion' => true]);

                        Notification::make()
                            ->title(__('analytics_events.marked_as_conversion_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('unmark_conversion')
                    ->label(__('analytics_events.unmark_conversion'))
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (AnalyticsEvent $record): bool => $record->is_conversion)
                    ->action(function (AnalyticsEvent $record): void {
                        $record->update(['is_conversion' => false]);

                        Notification::make()
                            ->title(__('analytics_events.unmarked_as_conversion_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mark_important')
                        ->label(__('analytics_events.mark_important_selected'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_important' => true]);
                            Notification::make()
                                ->title(__('analytics_events.bulk_marked_as_important_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unmark_important')
                        ->label(__('analytics_events.unmark_important_selected'))
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_important' => false]);

                            Notification::make()
                                ->title(__('analytics_events.bulk_unmarked_as_important_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('mark_conversions')
                        ->label(__('analytics_events.mark_conversions_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_conversion' => true]);

                            Notification::make()
                                ->title(__('analytics_events.bulk_marked_as_conversions_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unmark_conversions')
                        ->label(__('analytics_events.unmark_conversions_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_conversion' => false]);

                            Notification::make()
                                ->title(__('analytics_events.bulk_unmarked_as_conversions_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalyticsEvents::route('/'),
            'create' => Pages\CreateAnalyticsEvent::route('/create'),
            'view' => Pages\ViewAnalyticsEvent::route('/{record}'),
            'edit' => Pages\EditAnalyticsEvent::route('/{record}/edit'),
        ];
    }
}
