<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Filament\Resources\CampaignClickResource\Pages;
use BackedEnum;
use App\Models\CampaignClick;
use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Exports\ExcelExport;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
/**
 * CampaignClickResource
 * 
 * Filament v4 resource for CampaignClickResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property string|null $navigationLabel
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @property mixed $navigationGroup
 * @property int|null $navigationSort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class CampaignClickResource extends Resource
{
    protected static ?string $model = CampaignClick::class;
    /**
     * @var BackedEnum|string|null
     */
    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-cursor-arrow-rays';
    protected static ?string $navigationLabel = 'Campaign Clicks';
    protected static ?string $modelLabel = 'Campaign Click';
    protected static ?string $pluralModelLabel = 'Campaign Clicks';
    /** @var BackedEnum|string|null */
    /**
     * @var UnitEnum|string|null
     */
    /** @var UnitEnum|string|null */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Campaigns';
    protected static ?int $navigationSort = 3;
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Section::make(__('campaign_clicks.basic_information'))->schema([Grid::make(2)->schema([Select::make('campaign_id')->label(__('campaign_clicks.campaign'))->relationship('campaign', 'name')->searchable()->preload()->required(), Select::make('customer_id')->label(__('campaign_clicks.customer'))->relationship('customer', 'name')->searchable()->preload()]), Grid::make(2)->schema([TextInput::make('session_id')->label(__('campaign_clicks.session_id'))->maxLength(255), TextInput::make('ip_address')->label(__('campaign_clicks.ip_address'))->maxLength(45)]), TextInput::make('user_agent')->label(__('campaign_clicks.user_agent'))->maxLength(500)]), Section::make(__('campaign_clicks.click_details'))->schema([Grid::make(2)->schema([Select::make('click_type')->label(__('campaign_clicks.click_type'))->options(['cta' => __('campaign_clicks.click_type.cta'), 'banner' => __('campaign_clicks.click_type.banner'), 'link' => __('campaign_clicks.click_type.link'), 'button' => __('campaign_clicks.click_type.button'), 'image' => __('campaign_clicks.click_type.image')])->default('cta')->required(), TextInput::make('clicked_url')->label(__('campaign_clicks.clicked_url'))->url()->maxLength(500)]), DateTimePicker::make('clicked_at')->label(__('campaign_clicks.clicked_at'))->default(now())->required()]), Section::make(__('campaign_clicks.device_information'))->schema([Grid::make(3)->schema([Select::make('device_type')->label(__('campaign_clicks.device_type'))->options(['desktop' => __('campaign_clicks.device_type.desktop'), 'mobile' => __('campaign_clicks.device_type.mobile'), 'tablet' => __('campaign_clicks.device_type.tablet')]), TextInput::make('browser')->label(__('campaign_clicks.browser'))->maxLength(100), TextInput::make('os')->label(__('campaign_clicks.os'))->maxLength(100)]), TextInput::make('referer')->label(__('campaign_clicks.referer'))->url()->maxLength(500)]), Section::make(__('campaign_clicks.location_information'))->schema([Grid::make(2)->schema([TextInput::make('country')->label(__('campaign_clicks.country'))->maxLength(100), TextInput::make('city')->label(__('campaign_clicks.city'))->maxLength(100)])]), Section::make(__('campaign_clicks.utm_parameters'))->schema([Grid::make(2)->schema([TextInput::make('utm_source')->label(__('campaign_clicks.utm_source'))->maxLength(100), TextInput::make('utm_medium')->label(__('campaign_clicks.utm_medium'))->maxLength(100)]), Grid::make(2)->schema([TextInput::make('utm_campaign')->label(__('campaign_clicks.utm_campaign'))->maxLength(100), TextInput::make('utm_term')->label(__('campaign_clicks.utm_term'))->maxLength(100)]), TextInput::make('utm_content')->label(__('campaign_clicks.utm_content'))->maxLength(100)]), Section::make(__('campaign_clicks.conversion_tracking'))->schema([Grid::make(2)->schema([TextInput::make('conversion_value')->label(__('campaign_clicks.conversion_value'))->numeric()->prefix('€')->step(0.01), Toggle::make('is_converted')->label(__('campaign_clicks.is_converted'))->default(false)]), Textarea::make('conversion_data')->label(__('campaign_clicks.conversion_data'))->json()->rows(3)])]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('id')->label(__('campaign_clicks.id'))->sortable(), TextColumn::make('campaign.name')->label(__('campaign_clicks.campaign'))->searchable()->sortable()->limit(30), TextColumn::make('customer.name')->label(__('campaign_clicks.customer'))->searchable()->sortable()->limit(30)->placeholder(__('campaign_clicks.guest')), BadgeColumn::make('click_type')->label(__('campaign_clicks.click_type'))->colors(['primary' => 'cta', 'success' => 'banner', 'warning' => 'link', 'info' => 'button', 'secondary' => 'image'])->formatStateUsing(fn(string $state): string => match ($state) {
            'cta' => __('campaign_clicks.click_type.cta'),
            'banner' => __('campaign_clicks.click_type.banner'),
            'link' => __('campaign_clicks.click_type.link'),
            'button' => __('campaign_clicks.click_type.button'),
            'image' => __('campaign_clicks.click_type.image'),
            default => $state,
        }), TextColumn::make('clicked_url')->label(__('campaign_clicks.clicked_url'))->limit(40)->tooltip(function (TextColumn $column): ?string {
            $state = $column->getState();
            return strlen($state) > 40 ? $state : null;
        }), BadgeColumn::make('device_type')->label(__('campaign_clicks.device_type'))->colors(['primary' => 'desktop', 'success' => 'mobile', 'warning' => 'tablet'])->formatStateUsing(fn(?string $state): string => match ($state) {
            'desktop' => __('campaign_clicks.device_type.desktop'),
            'mobile' => __('campaign_clicks.device_type.mobile'),
            'tablet' => __('campaign_clicks.device_type.tablet'),
            default => __('campaign_clicks.device_type.unknown'),
        }), TextColumn::make('browser')->label(__('campaign_clicks.browser'))->limit(20), TextColumn::make('country')->label(__('campaign_clicks.country'))->sortable()->searchable(), TextColumn::make('utm_source')->label(__('campaign_clicks.utm_source'))->sortable()->searchable()->limit(20), IconColumn::make('is_converted')->label(__('campaign_clicks.converted'))->boolean()->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-x-circle')->trueColor('success')->falseColor('danger'), TextColumn::make('conversion_value')->label(__('campaign_clicks.conversion_value'))->money('EUR')->sortable(), TextColumn::make('clicked_at')->label(__('campaign_clicks.clicked_at'))->dateTime()->sortable()->since()])->filters([SelectFilter::make('campaign_id')->label(__('campaign_clicks.campaign'))->relationship('campaign', 'name')->searchable()->preload(), SelectFilter::make('click_type')->label(__('campaign_clicks.click_type'))->options(['cta' => __('campaign_clicks.click_type.cta'), 'banner' => __('campaign_clicks.click_type.banner'), 'link' => __('campaign_clicks.click_type.link'), 'button' => __('campaign_clicks.click_type.button'), 'image' => __('campaign_clicks.click_type.image')]), SelectFilter::make('device_type')->label(__('campaign_clicks.device_type'))->options(['desktop' => __('campaign_clicks.device_type.desktop'), 'mobile' => __('campaign_clicks.device_type.mobile'), 'tablet' => __('campaign_clicks.device_type.tablet')]), SelectFilter::make('is_converted')->label(__('campaign_clicks.converted'))->options(['1' => __('campaign_clicks.yes'), '0' => __('campaign_clicks.no')]), Filter::make('has_customer')->label(__('campaign_clicks.has_customer'))->query(fn(Builder $query): Builder => $query->whereNotNull('customer_id')), Filter::make('guest_clicks')->label(__('campaign_clicks.guest_clicks'))->query(fn(Builder $query): Builder => $query->whereNull('customer_id')), DateFilter::make('clicked_at')->label(__('campaign_clicks.clicked_at')), Filter::make('recent_clicks')->label(__('campaign_clicks.recent_clicks'))->query(fn(Builder $query): Builder => $query->where('clicked_at', '>=', now()->subDays(7)))])->actions([ViewAction::make(), EditAction::make(), DeleteAction::make()])->bulkActions([BulkActionGroup::make([DeleteBulkAction::make(), ExportBulkAction::make()->exporter(ExcelExport::class)->fileName('campaign_clicks_export')])])->defaultSort('clicked_at', 'desc');
    }
    /**
     * Handle getRelations functionality with proper error handling.
     * @return array
     */
    public static function getRelations(): array
    {
        return [];
    }
    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return ['index' => Pages\ListCampaignClicks::route('/'), 'create' => Pages\CreateCampaignClick::route('/create'), 'view' => Pages\ViewCampaignClick::route('/{record}'), 'edit' => Pages\EditCampaignClick::route('/{record}/edit')];
    }
    /**
     * Handle getEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['campaign', 'customer']);
    }
}