<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CampaignConversionResource\Pages;
use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use UnitEnum;
use Filament\Schemas\Schema;

/**
 * CampaignConversionResource
 *
 * Filament v4 resource for CampaignConversion management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CampaignConversionResource extends Resource
{
    use HasNav;

    protected static ?string $model = CampaignConversion::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-rocket-launch';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Campaigns;

    public static function getNavigationLabel(): string
    {
        return __('campaign_conversions.title');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return self::$navigationGroup instanceof NavigationGroup
            ? self::$navigationGroup->label()
            : self::$navigationGroup;
    }

    public static function getPluralModelLabel(): string
    {
        return __('campaign_conversions.plural');
    }

    public static function getModelLabel(): string
    {
        return __('campaign_conversions.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $form): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return $schema->schema([
            Section::make(__('campaign_conversions.basic_information'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('campaign_id')
                                ->label(__('campaign_conversions.form.campaign_id'))
                                ->relationship('campaign', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set): void {
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
                                ->label(__('campaign_conversions.form.customer_id'))
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set): void {
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
                    Grid::make(2)
                        ->schema([
                            TextInput::make('conversion_value')
                                ->label(__('campaign_conversions.form.conversion_value'))
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->prefix('€'),
                            DateTimePicker::make('converted_at')
                                ->label(__('campaign_conversions.form.converted_at'))
                                ->required()
                                ->seconds(false),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('session_id')
                                ->label(__('campaign_conversions.form.session_id'))
                                ->maxLength(255),
                            TextInput::make('conversion_rate')
                                ->label(__('campaign_conversions.form.conversion_rate'))
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(1)
                                ->step(0.0001),
                        ]),
                ]),
            Section::make(__('campaign_conversions.form.tracking_information'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('source')
                                ->label(__('campaign_conversions.form.source'))
                                ->maxLength(255),
                            TextInput::make('medium')
                                ->label(__('campaign_conversions.form.medium'))
                                ->maxLength(255),
                            TextInput::make('campaign_name')
                                ->label(__('campaign_conversions.form.campaign_name'))
                                ->maxLength(255),
                            TextInput::make('utm_content')
                                ->label(__('campaign_conversions.form.utm_content'))
                                ->maxLength(255),
                            TextInput::make('utm_term')
                                ->label(__('campaign_conversions.form.utm_term'))
                                ->maxLength(255),
                            TextInput::make('referrer')
                                ->label(__('campaign_conversions.form.referrer'))
                                ->maxLength(500),
                        ]),
                ]),
            Section::make(__('campaign_conversions.form.device_information'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('device_type')
                                ->label(__('campaign_conversions.form.device_type'))
                                ->options([
                                    'mobile' => __('campaign_conversions.device_types.mobile'),
                                    'tablet' => __('campaign_conversions.device_types.tablet'),
                                    'desktop' => __('campaign_conversions.device_types.desktop'),
                                    'unknown' => __('campaign_conversions.device_types.unknown'),
                                ])
                                ->native(false),
                            TextInput::make('browser')
                                ->label(__('campaign_conversions.form.browser'))
                                ->maxLength(255),
                            TextInput::make('os')
                                ->label(__('campaign_conversions.form.os'))
                                ->maxLength(255),
                            TextInput::make('country')
                                ->label(__('campaign_conversions.form.country'))
                                ->maxLength(2),
                            TextInput::make('city')
                                ->label(__('campaign_conversions.form.city'))
                                ->maxLength(255),
                        ]),
                    Grid::make(3)
                        ->schema([
                            Toggle::make('is_mobile')
                                ->label(__('campaign_conversions.form.is_mobile')),
                            Toggle::make('is_tablet')
                                ->label(__('campaign_conversions.form.is_tablet')),
                            Toggle::make('is_desktop')
                                ->label(__('campaign_conversions.form.is_desktop')),
                        ]),
                ]),
            Section::make(__('campaign_conversions.form.additional_information'))
                ->schema([
                    Textarea::make('notes')
                        ->label(__('campaign_conversions.form.notes'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('campaign_conversions.table.id'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label(__('campaign_conversions.user'))
                    ->sortable(),
                IconColumn::make('is_converted')
                    ->label(__('campaign_conversions.is_converted'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('conversion_type')
                    ->label(__('campaign_conversions.table.conversion_type'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => $state ? __('campaign_conversions.conversion_types.'.$state) : '-')
                    ->toggleable(),
                TextColumn::make('conversion_value')
                    ->label(__('campaign_conversions.conversion_value'))
                    ->formatStateUsing(fn ($state, CampaignConversion $record) => Number::currency((float) $state, $record->conversion_currency ?? 'EUR', locale: app()->getLocale()))
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label(__('campaign_conversions.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_conversions.filters.campaign'))
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('conversion_type')
                    ->label(__('campaign_conversions.filters.conversion_type'))
                    ->options([
                        'purchase' => __('campaign_conversions.conversion_types.purchase'),
                        'signup' => __('campaign_conversions.conversion_types.signup'),
                        'download' => __('campaign_conversions.conversion_types.download'),
                        'subscription' => __('campaign_conversions.conversion_types.subscription'),
                        'lead' => __('campaign_conversions.conversion_types.lead'),
                        'trial' => __('campaign_conversions.conversion_types.trial'),
                        'custom' => __('campaign_conversions.conversion_types.custom'),
                    ])
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            InfolistSection::make(__('campaign_conversions.infolist.basic_information'))
                ->schema([
                    InfolistGrid::make(2)
                        ->schema([
                            TextEntry::make('id')
                                ->label(__('campaign_conversions.infolist.id')),
                            TextEntry::make('campaign.name')
                                ->label(__('campaign_conversions.infolist.campaign')),
                            TextEntry::make('conversion_type')
                                ->label(__('campaign_conversions.infolist.conversion_type'))
                                ->formatStateUsing(fn (?string $state): string => $state ? __('campaign_conversions.conversion_types.'.$state) : '-'),
                            TextEntry::make('conversion_value')
                                ->label(__('campaign_conversions.infolist.conversion_value'))
                                ->formatStateUsing(fn ($state): string => '€'.number_format((float) $state, 2)),
                            TextEntry::make('status')
                                ->label(__('campaign_conversions.infolist.status'))
                                ->formatStateUsing(fn (?string $state): string => $state ? __('campaign_conversions.statuses.'.$state) : '-'),
                            TextEntry::make('converted_at')
                                ->label(__('campaign_conversions.infolist.converted_at'))
                                ->dateTime(),
                        ]),
                ]),
            InfolistSection::make(__('campaign_conversions.infolist.customer_information'))
                ->schema([
                    InfolistGrid::make(2)
                        ->schema([
                            TextEntry::make('customer.name')
                                ->label(__('campaign_conversions.infolist.customer_name')),
                            TextEntry::make('customer.email')
                                ->label(__('campaign_conversions.infolist.customer_email')),
                            TextEntry::make('order_id')
                                ->label(__('campaign_conversions.infolist.order_id')),
                            TextEntry::make('session_id')
                                ->label(__('campaign_conversions.infolist.session_id')),
                        ]),
                ]),
            InfolistSection::make(__('campaign_conversions.infolist.tracking_information'))
                ->schema([
                    InfolistGrid::make(2)
                        ->schema([
                            TextEntry::make('source')
                                ->label(__('campaign_conversions.infolist.source')),
                            TextEntry::make('medium')
                                ->label(__('campaign_conversions.infolist.medium')),
                            TextEntry::make('campaign_name')
                                ->label(__('campaign_conversions.infolist.campaign_name')),
                            TextEntry::make('utm_content')
                                ->label(__('campaign_conversions.infolist.utm_content')),
                            TextEntry::make('utm_term')
                                ->label(__('campaign_conversions.infolist.utm_term')),
                            TextEntry::make('referrer')
                                ->label(__('campaign_conversions.infolist.referrer')),
                        ]),
                ]),
            InfolistSection::make(__('campaign_conversions.infolist.device_information'))
                ->schema([
                    InfolistGrid::make(2)
                        ->schema([
                            TextEntry::make('device_type')
                                ->label(__('campaign_conversions.infolist.device_type'))
                                ->formatStateUsing(fn (?string $state): string => $state ? __('campaign_conversions.device_types.'.$state) : '-'),
                            TextEntry::make('browser')
                                ->label(__('campaign_conversions.infolist.browser')),
                            TextEntry::make('os')
                                ->label(__('campaign_conversions.infolist.os')),
                            TextEntry::make('country')
                                ->label(__('campaign_conversions.infolist.country')),
                            TextEntry::make('city')
                                ->label(__('campaign_conversions.infolist.city')),
                            TextEntry::make('ip_address')
                                ->label(__('campaign_conversions.infolist.ip_address')),
                        ]),
                ]),
            InfolistSection::make(__('campaign_conversions.infolist.performance_metrics'))
                ->schema([
                    InfolistGrid::make(2)
                        ->schema([
                            TextEntry::make('roi')
                                ->label(__('campaign_conversions.infolist.roi'))
                                ->formatStateUsing(fn ($state): string => $state === null ? '-' : number_format((float) $state * 100, 2).' %'),
                            TextEntry::make('roas')
                                ->label(__('campaign_conversions.infolist.roas')),
                            TextEntry::make('cost_per_conversion')
                                ->label(__('campaign_conversions.infolist.cost_per_conversion'))
                                ->formatStateUsing(fn ($state): string => $state === null ? '-' : '€'.number_format((float) $state, 2)),
                            TextEntry::make('lifetime_value')
                                ->label(__('campaign_conversions.infolist.lifetime_value'))
                                ->formatStateUsing(fn ($state): string => $state === null ? '-' : '€'.number_format((float) $state, 2)),
                            TextEntry::make('customer_acquisition_cost')
                                ->label(__('campaign_conversions.infolist.customer_acquisition_cost'))
                                ->formatStateUsing(fn ($state): string => $state === null ? '-' : '€'.number_format((float) $state, 2)),
                            TextEntry::make('conversion_rate')
                                ->label(__('campaign_conversions.infolist.conversion_rate'))
                                ->formatStateUsing(fn ($state): string => $state === null ? '-' : number_format((float) $state * 100, 2).' %'),
                        ]),
                ]),
            InfolistSection::make(__('campaign_conversions.infolist.additional_information'))
                ->schema([
                    TextEntry::make('notes')
                        ->label(__('campaign_conversions.infolist.notes'))
                        ->columnSpanFull()
                        ->placeholder('-'),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCampaignConversions::route('/'),
            'create' => Pages\CreateCampaignConversion::route('/create'),
            'edit'   => Pages\EditCampaignConversion::route('/{record}/edit'),
            'view'   => Pages\ViewCampaignConversion::route('/{record}'),
        ];
    }
}