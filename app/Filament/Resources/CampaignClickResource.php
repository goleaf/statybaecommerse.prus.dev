<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignClickResource\Pages;
use App\Models\CampaignClick;
use UnitEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Exports\ExcelExport;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class CampaignClickResource extends Resource
{
    protected static ?string $model = CampaignClick::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-cursor-arrow-rays';

    protected static ?string $navigationLabel = 'Campaign Clicks';

    protected static ?string $modelLabel = 'Campaign Click';

    protected static ?string $pluralModelLabel = 'Campaign Clicks';

    /**
     * @var string|\BackedEnum|null
     */
    protected static UnitEnum|string|null $navigationGroup = 'Campaigns';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('campaign_clicks.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('campaign_id')
                                    ->label(__('campaign_clicks.campaign'))
                                    ->relationship('campaign', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('customer_id')
                                    ->label(__('campaign_clicks.customer'))
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('session_id')
                                    ->label(__('campaign_clicks.session_id'))
                                    ->maxLength(255),
                                TextInput::make('ip_address')
                                    ->label(__('campaign_clicks.ip_address'))
                                    ->maxLength(45),
                            ]),
                        TextInput::make('user_agent')
                            ->label(__('campaign_clicks.user_agent'))
                            ->maxLength(500),
                    ]),
                Section::make(__('campaign_clicks.click_details'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('click_type')
                                    ->label(__('campaign_clicks.click_type'))
                                    ->options([
                                        'cta' => __('campaign_clicks.click_type.cta'),
                                        'banner' => __('campaign_clicks.click_type.banner'),
                                        'link' => __('campaign_clicks.click_type.link'),
                                        'button' => __('campaign_clicks.click_type.button'),
                                        'image' => __('campaign_clicks.click_type.image'),
                                    ])
                                    ->default('cta')
                                    ->required(),
                                TextInput::make('clicked_url')
                                    ->label(__('campaign_clicks.clicked_url'))
                                    ->url()
                                    ->maxLength(500),
                            ]),
                        DateTimePicker::make('clicked_at')
                            ->label(__('campaign_clicks.clicked_at'))
                            ->default(now())
                            ->required(),
                    ]),
                Section::make(__('campaign_clicks.device_information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('device_type')
                                    ->label(__('campaign_clicks.device_type'))
                                    ->options([
                                        'desktop' => __('campaign_clicks.device_type.desktop'),
                                        'mobile' => __('campaign_clicks.device_type.mobile'),
                                        'tablet' => __('campaign_clicks.device_type.tablet'),
                                    ]),
                                TextInput::make('browser')
                                    ->label(__('campaign_clicks.browser'))
                                    ->maxLength(100),
                                TextInput::make('os')
                                    ->label(__('campaign_clicks.os'))
                                    ->maxLength(100),
                            ]),
                        TextInput::make('referer')
                            ->label(__('campaign_clicks.referer'))
                            ->url()
                            ->maxLength(500),
                    ]),
                Section::make(__('campaign_clicks.location_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('country')
                                    ->label(__('campaign_clicks.country'))
                                    ->maxLength(100),
                                TextInput::make('city')
                                    ->label(__('campaign_clicks.city'))
                                    ->maxLength(100),
                            ]),
                    ]),
                Section::make(__('campaign_clicks.utm_parameters'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('utm_source')
                                    ->label(__('campaign_clicks.utm_source'))
                                    ->maxLength(100),
                                TextInput::make('utm_medium')
                                    ->label(__('campaign_clicks.utm_medium'))
                                    ->maxLength(100),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('utm_campaign')
                                    ->label(__('campaign_clicks.utm_campaign'))
                                    ->maxLength(100),
                                TextInput::make('utm_term')
                                    ->label(__('campaign_clicks.utm_term'))
                                    ->maxLength(100),
                            ]),
                        TextInput::make('utm_content')
                            ->label(__('campaign_clicks.utm_content'))
                            ->maxLength(100),
                    ]),
                Section::make(__('campaign_clicks.conversion_tracking'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('conversion_value')
                                    ->label(__('campaign_clicks.conversion_value'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                Toggle::make('is_converted')
                                    ->label(__('campaign_clicks.is_converted'))
                                    ->default(false),
                            ]),
                        Textarea::make('conversion_data')
                            ->label(__('campaign_clicks.conversion_data'))
                            ->json()
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('campaign_clicks.id'))
                    ->sortable(),
                TextColumn::make('campaign.name')
                    ->label(__('campaign_clicks.campaign'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('customer.name')
                    ->label(__('campaign_clicks.customer'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder(__('campaign_clicks.guest')),
                BadgeColumn::make('click_type')
                    ->label(__('campaign_clicks.click_type'))
                    ->colors([
                        'primary' => 'cta',
                        'success' => 'banner',
                        'warning' => 'link',
                        'info' => 'button',
                        'secondary' => 'image',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cta' => __('campaign_clicks.click_type.cta'),
                        'banner' => __('campaign_clicks.click_type.banner'),
                        'link' => __('campaign_clicks.click_type.link'),
                        'button' => __('campaign_clicks.click_type.button'),
                        'image' => __('campaign_clicks.click_type.image'),
                        default => $state,
                    }),
                TextColumn::make('clicked_url')
                    ->label(__('campaign_clicks.clicked_url'))
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    }),
                BadgeColumn::make('device_type')
                    ->label(__('campaign_clicks.device_type'))
                    ->colors([
                        'primary' => 'desktop',
                        'success' => 'mobile',
                        'warning' => 'tablet',
                    ])
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'desktop' => __('campaign_clicks.device_type.desktop'),
                        'mobile' => __('campaign_clicks.device_type.mobile'),
                        'tablet' => __('campaign_clicks.device_type.tablet'),
                        default => __('campaign_clicks.device_type.unknown'),
                    }),
                TextColumn::make('browser')
                    ->label(__('campaign_clicks.browser'))
                    ->limit(20),
                TextColumn::make('country')
                    ->label(__('campaign_clicks.country'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('utm_source')
                    ->label(__('campaign_clicks.utm_source'))
                    ->sortable()
                    ->searchable()
                    ->limit(20),
                IconColumn::make('is_converted')
                    ->label(__('campaign_clicks.converted'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('conversion_value')
                    ->label(__('campaign_clicks.conversion_value'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('clicked_at')
                    ->label(__('campaign_clicks.clicked_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_clicks.campaign'))
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('click_type')
                    ->label(__('campaign_clicks.click_type'))
                    ->options([
                        'cta' => __('campaign_clicks.click_type.cta'),
                        'banner' => __('campaign_clicks.click_type.banner'),
                        'link' => __('campaign_clicks.click_type.link'),
                        'button' => __('campaign_clicks.click_type.button'),
                        'image' => __('campaign_clicks.click_type.image'),
                    ]),
                SelectFilter::make('device_type')
                    ->label(__('campaign_clicks.device_type'))
                    ->options([
                        'desktop' => __('campaign_clicks.device_type.desktop'),
                        'mobile' => __('campaign_clicks.device_type.mobile'),
                        'tablet' => __('campaign_clicks.device_type.tablet'),
                    ]),
                SelectFilter::make('is_converted')
                    ->label(__('campaign_clicks.converted'))
                    ->options([
                        '1' => __('campaign_clicks.yes'),
                        '0' => __('campaign_clicks.no'),
                    ]),
                Filter::make('has_customer')
                    ->label(__('campaign_clicks.has_customer'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('customer_id')),
                Filter::make('guest_clicks')
                    ->label(__('campaign_clicks.guest_clicks'))
                    ->query(fn(Builder $query): Builder => $query->whereNull('customer_id')),
                DateFilter::make('clicked_at')
                    ->label(__('campaign_clicks.clicked_at')),
                Filter::make('recent_clicks')
                    ->label(__('campaign_clicks.recent_clicks'))
                    ->query(fn(Builder $query): Builder => $query->where('clicked_at', '>=', now()->subDays(7))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(ExcelExport::class)
                        ->fileName('campaign_clicks_export'),
                ]),
            ])
            ->defaultSort('clicked_at', 'desc');
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
            'index' => Pages\ListCampaignClicks::route('/'),
            'create' => Pages\CreateCampaignClick::route('/create'),
            'view' => Pages\ViewCampaignClick::route('/{record}'),
            'edit' => Pages\EditCampaignClick::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['campaign', 'customer']);
    }
}
