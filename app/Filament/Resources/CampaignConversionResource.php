<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CampaignConversionResource\Pages;
use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * CampaignConversionResource
 *
 * Filament v4 resource for CampaignConversion management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CampaignConversionResource extends Resource
{
    protected static ?string $model = CampaignConversion::class;

    /**
     * @var UnitEnum|string|null
     */
    /*protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Marketing;

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'campaign_name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('campaign_conversions.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Marketing'->value;
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('campaign_conversions.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('campaign_conversions.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('campaign_conversions.basic_information'))
                ->components([
                    Grid::make(2)
                        ->schema([
                            Select::make('campaign_id')
                                ->label(__('campaign_conversions.campaign'))
                                ->relationship('campaign', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $campaign = Campaign::find($state);
                                        if ($campaign) {
                                            $set('campaign_name', $campaign->name);
                                            $set('campaign_code', $campaign->code);
                                        }
                                    }
                                }),
                            TextInput::make('campaign_name')
                                ->label(__('campaign_conversions.campaign_name'))
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('customer_id')
                                ->label(__('campaign_conversions.user'))
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('user_name', $user->name);
                                            $set('user_email', $user->email);
                                        }
                                    }
                                }),
                            TextInput::make('user_name')
                                ->label(__('campaign_conversions.user_name'))
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                ]),
            Section::make(__('campaign_conversions.conversion_information'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('conversion_type')
                                ->label(__('campaign_conversions.type'))
                                ->options([
                                    'purchase' => __('campaign_conversions.types.purchase'),
                                    'signup' => __('campaign_conversions.types.signup'),
                                    'download' => __('campaign_conversions.types.download'),
                                    'subscription' => __('campaign_conversions.types.subscription'),
                                    'lead' => __('campaign_conversions.types.lead'),
                                    'custom' => __('campaign_conversions.types.custom'),
                                ])
                                ->default('purchase')
                                ->required(),
                            TextInput::make('conversion_value')
                                ->label(__('campaign_conversions.value'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('campaign_conversions.value_help')),
                            TextInput::make('currency')
                                ->label(__('campaign_conversions.currency'))
                                ->maxLength(3)
                                ->default('EUR')
                                ->rules(['alpha']),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('status')
                                ->label(__('campaign_conversions.status'))
                                ->options([
                                    'completed' => __('campaign_conversions.statuses.completed'),
                                    'pending' => __('campaign_conversions.statuses.pending'),
                                    'confirmed' => __('campaign_conversions.statuses.confirmed'),
                                    'cancelled' => __('campaign_conversions.statuses.cancelled'),
                                ])
                                ->default('completed')
                                ->required(),
                            Textarea::make('description')
                                ->label(__('campaign_conversions.description'))
                                ->rows(3)
                                ->maxLength(500)
                                ->columnSpanFull(),
                        ]),
                ]),
            Section::make(__('campaign_conversions.tracking_information'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('ip_address')
                                ->label(__('campaign_conversions.ip_address'))
                                ->maxLength(45)
                                ->rules(['ip'])
                                ->helperText(__('campaign_conversions.ip_address_help')),
                            TextInput::make('user_agent')
                                ->label(__('campaign_conversions.user_agent'))
                                ->maxLength(500)
                                ->helperText(__('campaign_conversions.user_agent_help')),
                            TextInput::make('device_type')
                                ->label(__('campaign_conversions.device_type'))
                                ->maxLength(50)
                                ->helperText(__('campaign_conversions.device_type_help')),
                        ]),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('browser')
                                ->label(__('campaign_conversions.browser'))
                                ->helperText(__('campaign_conversions.browser_help')),
                            TextInput::make('os')
                                ->label(__('campaign_conversions.os'))
                                ->helperText(__('campaign_conversions.os_help')),
                            TextInput::make('country')
                                ->label(__('campaign_conversions.country'))
                                ->helperText(__('campaign_conversions.country_help')),
                        ]),
                ]),
            Section::make(__('campaign_conversions.attribution'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('utm_source')
                                ->label(__('campaign_conversions.utm_source'))
                                ->helperText(__('campaign_conversions.utm_source_help')),
                            TextInput::make('utm_medium')
                                ->label(__('campaign_conversions.utm_medium'))
                                ->helperText(__('campaign_conversions.utm_medium_help')),
                            TextInput::make('utm_campaign')
                                ->label(__('campaign_conversions.utm_campaign'))
                                ->helperText(__('campaign_conversions.utm_campaign_help')),
                        ]),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('utm_term')
                                ->label(__('campaign_conversions.utm_term'))
                                ->helperText(__('campaign_conversions.utm_term_help')),
                            TextInput::make('utm_content')
                                ->label(__('campaign_conversions.utm_content'))
                                ->helperText(__('campaign_conversions.utm_content_help')),
                            TextInput::make('referrer_url')
                                ->label(__('campaign_conversions.referrer_url'))
                                ->url()
                                ->helperText(__('campaign_conversions.referrer_url_help')),
                        ]),
                ]),
            Section::make(__('campaign_conversions.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_verified')
                                ->label(__('campaign_conversions.is_verified'))
                                ->default(false),
                            Toggle::make('is_attributed')
                                ->label(__('campaign_conversions.is_attributed'))
                                ->default(true),
                        ]),
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('converted_at')
                                ->label(__('campaign_conversions.converted_at'))
                                ->default(now())
                                ->displayFormat('d/m/Y H:i'),
                            TextInput::make('attribution_window')
                                ->label(__('campaign_conversions.attribution_window'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(365)
                                ->default(30)
                                ->suffix('days')
                                ->helperText(__('campaign_conversions.attribution_window_help')),
                        ]),
                    Textarea::make('notes')
                        ->label(__('campaign_conversions.notes'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('campaign_name')
                    ->label(__('campaign_conversions.campaign_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                TextColumn::make('campaign_code')
                    ->label(__('campaign_conversions.campaign_code'))
                    ->copyable()
                    ->badge()
                    ->color('blue'),
                TextColumn::make('customer.name')
                    ->label(__('campaign_conversions.user'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('conversion_type')
                    ->label(__('campaign_conversions.type'))
                    ->formatStateUsing(fn(string $state): string => __("campaign_conversions.types.{$state}"))
                    ->color(fn(string $state): string => match ($state) {
                        'purchase' => 'green',
                        'signup' => 'blue',
                        'download' => 'purple',
                        'subscription' => 'orange',
                        'lead' => 'pink',
                        'custom' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('conversion_value')
                    ->label(__('campaign_conversions.value'))
                    ->money('EUR')
                    ->alignCenter(),
                TextColumn::make('status')
                    ->label(__('campaign_conversions.status'))
                    ->formatStateUsing(fn(string $state): string => __("campaign_conversions.statuses.{$state}"))
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->badge(),
                TextColumn::make('currency')
                    ->label(__('campaign_conversions.currency'))
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('conversion_id')
                    ->label(__('campaign_conversions.conversion_id'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ip_address')
                    ->label(__('campaign_conversions.ip_address'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('device_type')
                    ->label(__('campaign_conversions.device_type'))
                    ->color('purple')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('browser')
                    ->label(__('campaign_conversions.browser'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('os')
                    ->label(__('campaign_conversions.os'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country')
                    ->label(__('campaign_conversions.country'))
                    ->color('green')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('utm_source')
                    ->label(__('campaign_conversions.utm_source'))
                    ->color('orange')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('utm_medium')
                    ->label(__('campaign_conversions.utm_medium'))
                    ->color('teal')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('utm_campaign')
                    ->label(__('campaign_conversions.utm_campaign'))
                    ->color('indigo')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_verified')
                    ->label(__('campaign_conversions.is_verified'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_attributed')
                    ->label(__('campaign_conversions.is_attributed'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('converted_at')
                    ->label(__('campaign_conversions.converted_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('attribution_window')
                    ->label(__('campaign_conversions.attribution_window'))
                    ->numeric()
                    ->suffix(' days')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_conversions.campaign'))
                    ->relationship('campaign', 'name')
                    ->preload(),
                SelectFilter::make('customer_id')
                    ->label(__('campaign_conversions.user'))
                    ->relationship('customer', 'name')
                    ->preload(),
                SelectFilter::make('conversion_type')
                    ->label(__('campaign_conversions.type'))
                    ->options([
                        'purchase' => __('campaign_conversions.types.purchase'),
                        'signup' => __('campaign_conversions.types.signup'),
                        'download' => __('campaign_conversions.types.download'),
                        'subscription' => __('campaign_conversions.types.subscription'),
                        'lead' => __('campaign_conversions.types.lead'),
                        'custom' => __('campaign_conversions.types.custom'),
                    ]),
                SelectFilter::make('status')
                    ->label(__('campaign_conversions.status'))
                    ->options([
                        'completed' => __('campaign_conversions.statuses.completed'),
                        'pending' => __('campaign_conversions.statuses.pending'),
                        'confirmed' => __('campaign_conversions.statuses.confirmed'),
                        'cancelled' => __('campaign_conversions.statuses.cancelled'),
                    ]),
                TernaryFilter::make('is_verified')
                    ->label(__('campaign_conversions.is_verified'))
                    ->trueLabel(__('campaign_conversions.verified_only'))
                    ->falseLabel(__('campaign_conversions.unverified_only'))
                    ->native(false),
                TernaryFilter::make('is_attributed')
                    ->label(__('campaign_conversions.is_attributed'))
                    ->trueLabel(__('campaign_conversions.attributed_only'))
                    ->falseLabel(__('campaign_conversions.unattributed_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('verify')
                    ->label(__('campaign_conversions.verify'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(CampaignConversion $record): bool => !$record->is_verified)
                    ->action(function (CampaignConversion $record): void {
                        $record->update(['is_verified' => true]);
                        Notification::make()
                            ->title(__('campaign_conversions.verified_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('unverify')
                    ->label(__('campaign_conversions.unverify'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn(CampaignConversion $record): bool => $record->is_verified)
                    ->action(function (CampaignConversion $record): void {
                        $record->update(['is_verified' => false]);
                        Notification::make()
                            ->title(__('campaign_conversions.unverified_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('attribute')
                    ->label(__('campaign_conversions.attribute'))
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->visible(fn(CampaignConversion $record): bool => !$record->is_attributed)
                    ->action(function (CampaignConversion $record): void {
                        $record->update(['is_attributed' => true]);
                        Notification::make()
                            ->title(__('campaign_conversions.attributed_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('unattribute')
                    ->label(__('campaign_conversions.unattribute'))
                    ->icon('heroicon-o-link-slash')
                    ->color('warning')
                    ->visible(fn(CampaignConversion $record): bool => $record->is_attributed)
                    ->action(function (CampaignConversion $record): void {
                        $record->update(['is_attributed' => false]);
                        Notification::make()
                            ->title(__('campaign_conversions.unattributed_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('verify')
                        ->label(__('campaign_conversions.verify_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_verified' => true]);
                            Notification::make()
                                ->title(__('campaign_conversions.bulk_verified_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unverify')
                        ->label(__('campaign_conversions.unverify_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_verified' => false]);
                            Notification::make()
                                ->title(__('campaign_conversions.bulk_unverified_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('attribute')
                        ->label(__('campaign_conversions.attribute_selected'))
                        ->icon('heroicon-o-link')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_attributed' => true]);
                            Notification::make()
                                ->title(__('campaign_conversions.bulk_attributed_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unattribute')
                        ->label(__('campaign_conversions.unattribute_selected'))
                        ->icon('heroicon-o-link-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_attributed' => false]);
                            Notification::make()
                                ->title(__('campaign_conversions.bulk_unattributed_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ])
            ])
            ->defaultSort('converted_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     * @return array
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
            'index' => Pages\ListCampaignConversions::route('/'),
            'create' => Pages\CreateCampaignConversion::route('/create'),
            'view' => Pages\ViewCampaignConversion::route('/{record}'),
            'edit' => Pages\EditCampaignConversion::route('/{record}/edit'),
        ];
    }
}
