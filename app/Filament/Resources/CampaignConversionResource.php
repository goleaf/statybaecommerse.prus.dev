<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Filament\Resources\CampaignConversionResource\Pages;
use BackedEnum;
use App\Models\CampaignConversion;
use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TagsInput;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\KeyValueEntry;
use Filament\Schemas\Components\Section as InfolistSection;
use Filament\Schemas\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\NavigationGroup;
use UnitEnum;
/**
 * CampaignConversionResource
 * 
 * Filament v4 resource for CampaignConversionResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property string|null $navigationLabel
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @property int|null $navigationSort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class CampaignConversionResource extends Resource
{
    protected static ?string $model = CampaignConversion::class;
    /**
     * @var BackedEnum|string|null
     */
    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'campaign_conversions.navigation.label';
    protected static ?string $modelLabel = 'campaign_conversions.model.label';
    protected static ?string $pluralModelLabel = 'campaign_conversions.model.plural';
    /** @var BackedEnum|string|null */
    protected static ?int $navigationSort = 3;
    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Marketing->label();
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Section::make(__('campaign_conversions.form.basic_information'))->schema([Grid::make(3)->schema([Select::make('campaign_id')->label(__('campaign_conversions.form.campaign_id'))->relationship('campaign', 'name')->searchable()->preload()->required()->createOptionForm([TextInput::make('name')->label(__('campaigns.form.name'))->required()->maxLength(255), TextInput::make('slug')->label(__('campaigns.form.slug'))->required()->maxLength(255)]), Select::make('order_id')->label(__('campaign_conversions.form.order_id'))->relationship('order', 'id')->searchable()->preload(), Select::make('customer_id')->label(__('campaign_conversions.form.customer_id'))->relationship('customer', 'email')->searchable()->preload()])]), Section::make(__('campaign_conversions.form.conversion_details'))->schema([Grid::make(3)->schema([Select::make('conversion_type')->label(__('campaign_conversions.form.conversion_type'))->options(['purchase' => __('campaign_conversions.conversion_types.purchase'), 'signup' => __('campaign_conversions.conversion_types.signup'), 'download' => __('campaign_conversions.conversion_types.download'), 'lead' => __('campaign_conversions.conversion_types.lead'), 'subscription' => __('campaign_conversions.conversion_types.subscription'), 'trial' => __('campaign_conversions.conversion_types.trial'), 'custom' => __('campaign_conversions.conversion_types.custom')])->required()->default('purchase'), TextInput::make('conversion_value')->label(__('campaign_conversions.form.conversion_value'))->numeric()->prefix('€')->step(0.01)->required(), Select::make('status')->label(__('campaign_conversions.form.status'))->options(['pending' => __('campaign_conversions.statuses.pending'), 'completed' => __('campaign_conversions.statuses.completed'), 'cancelled' => __('campaign_conversions.statuses.cancelled'), 'refunded' => __('campaign_conversions.statuses.refunded')])->required()->default('completed')]), DateTimePicker::make('converted_at')->label(__('campaign_conversions.form.converted_at'))->required()->default(now()), TextInput::make('session_id')->label(__('campaign_conversions.form.session_id'))->maxLength(255)]), Section::make(__('campaign_conversions.form.tracking_information'))->schema([Grid::make(3)->schema([TextInput::make('source')->label(__('campaign_conversions.form.source'))->maxLength(255), TextInput::make('medium')->label(__('campaign_conversions.form.medium'))->maxLength(255), TextInput::make('campaign_name')->label(__('campaign_conversions.form.campaign_name'))->maxLength(255)]), Grid::make(2)->schema([TextInput::make('utm_content')->label(__('campaign_conversions.form.utm_content'))->maxLength(255), TextInput::make('utm_term')->label(__('campaign_conversions.form.utm_term'))->maxLength(255)]), TextInput::make('referrer')->label(__('campaign_conversions.form.referrer'))->url()->maxLength(500)]), Section::make(__('campaign_conversions.form.device_information'))->schema([Grid::make(3)->schema([Select::make('device_type')->label(__('campaign_conversions.form.device_type'))->options(['mobile' => __('campaign_conversions.device_types.mobile'), 'tablet' => __('campaign_conversions.device_types.tablet'), 'desktop' => __('campaign_conversions.device_types.desktop')]), TextInput::make('browser')->label(__('campaign_conversions.form.browser'))->maxLength(255), TextInput::make('os')->label(__('campaign_conversions.form.os'))->maxLength(255)]), Grid::make(3)->schema([Toggle::make('is_mobile')->label(__('campaign_conversions.form.is_mobile')), Toggle::make('is_tablet')->label(__('campaign_conversions.form.is_tablet')), Toggle::make('is_desktop')->label(__('campaign_conversions.form.is_desktop'))]), Grid::make(2)->schema([TextInput::make('country')->label(__('campaign_conversions.form.country'))->maxLength(255), TextInput::make('city')->label(__('campaign_conversions.form.city'))->maxLength(255)])]), Section::make(__('campaign_conversions.form.analytics'))->schema([Grid::make(3)->schema([TextInput::make('conversion_duration')->label(__('campaign_conversions.form.conversion_duration'))->numeric()->suffix(__('campaign_conversions.form.seconds')), TextInput::make('page_views')->label(__('campaign_conversions.form.page_views'))->numeric(), TextInput::make('time_on_site')->label(__('campaign_conversions.form.time_on_site'))->numeric()->suffix(__('campaign_conversions.form.seconds'))]), Grid::make(2)->schema([TextInput::make('bounce_rate')->label(__('campaign_conversions.form.bounce_rate'))->numeric()->step(0.01)->suffix('%'), TextInput::make('conversion_rate')->label(__('campaign_conversions.form.conversion_rate'))->numeric()->step(0.0001)->suffix('%')]), Grid::make(2)->schema([TextInput::make('exit_page')->label(__('campaign_conversions.form.exit_page'))->maxLength(500), TextInput::make('landing_page')->label(__('campaign_conversions.form.landing_page'))->maxLength(500)])]), Section::make(__('campaign_conversions.form.attribution'))->schema([Select::make('attribution_model')->label(__('campaign_conversions.form.attribution_model'))->options(['last_click' => __('campaign_conversions.attribution_models.last_click'), 'first_click' => __('campaign_conversions.attribution_models.first_click'), 'linear' => __('campaign_conversions.attribution_models.linear'), 'time_decay' => __('campaign_conversions.attribution_models.time_decay'), 'position_based' => __('campaign_conversions.attribution_models.position_based'), 'data_driven' => __('campaign_conversions.attribution_models.data_driven')])->default('last_click'), Grid::make(3)->schema([TextInput::make('last_click_attribution')->label(__('campaign_conversions.form.last_click_attribution'))->numeric()->prefix('€')->step(0.01), TextInput::make('first_click_attribution')->label(__('campaign_conversions.form.first_click_attribution'))->numeric()->prefix('€')->step(0.01), TextInput::make('linear_attribution')->label(__('campaign_conversions.form.linear_attribution'))->numeric()->prefix('€')->step(0.01)]), Grid::make(2)->schema([TextInput::make('conversion_window')->label(__('campaign_conversions.form.conversion_window'))->numeric()->suffix(__('campaign_conversions.form.days'))->default(30), TextInput::make('lookback_window')->label(__('campaign_conversions.form.lookback_window'))->numeric()->suffix(__('campaign_conversions.form.days'))->default(90)])]), Section::make(__('campaign_conversions.form.performance_metrics'))->schema([Grid::make(3)->schema([TextInput::make('cost_per_conversion')->label(__('campaign_conversions.form.cost_per_conversion'))->numeric()->prefix('€')->step(0.01), TextInput::make('roi')->label(__('campaign_conversions.form.roi'))->numeric()->step(0.0001)->suffix('%'), TextInput::make('roas')->label(__('campaign_conversions.form.roas'))->numeric()->step(0.0001)->suffix('%')]), Grid::make(3)->schema([TextInput::make('lifetime_value')->label(__('campaign_conversions.form.lifetime_value'))->numeric()->prefix('€')->step(0.01), TextInput::make('customer_acquisition_cost')->label(__('campaign_conversions.form.customer_acquisition_cost'))->numeric()->prefix('€')->step(0.01), TextInput::make('payback_period')->label(__('campaign_conversions.form.payback_period'))->numeric()->suffix(__('campaign_conversions.form.days'))])]), Section::make(__('campaign_conversions.form.additional_information'))->schema([Textarea::make('notes')->label(__('campaign_conversions.form.notes'))->rows(3)->columnSpanFull(), TagsInput::make('tags')->label(__('campaign_conversions.form.tags'))->columnSpanFull(), KeyValue::make('custom_attributes')->label(__('campaign_conversions.form.custom_attributes'))->columnSpanFull(), KeyValue::make('conversion_data')->label(__('campaign_conversions.form.conversion_data'))->columnSpanFull()])]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('id')->label(__('campaign_conversions.table.id'))->sortable()->searchable(), TextColumn::make('campaign.name')->label(__('campaign_conversions.table.campaign'))->sortable()->searchable()->limit(30), TextColumn::make('conversion_type')->label(__('campaign_conversions.table.conversion_type'))->badge()->color(fn(string $state): string => match ($state) {
            'purchase' => 'success',
            'signup' => 'info',
            'download' => 'warning',
            'lead' => 'primary',
            default => 'gray',
        })->formatStateUsing(fn(string $state): string => __("campaign_conversions.conversion_types.{$state}")), TextColumn::make('conversion_value')->label(__('campaign_conversions.table.conversion_value'))->money('EUR')->sortable()->weight(FontWeight::Bold), BadgeColumn::make('status')->label(__('campaign_conversions.table.status'))->colors(['warning' => 'pending', 'success' => 'completed', 'danger' => 'cancelled', 'secondary' => 'refunded'])->formatStateUsing(fn(string $state): string => __("campaign_conversions.statuses.{$state}")), TextColumn::make('customer.email')->label(__('campaign_conversions.table.customer'))->sortable()->searchable()->limit(25), TextColumn::make('source')->label(__('campaign_conversions.table.source'))->sortable()->searchable()->limit(20), TextColumn::make('medium')->label(__('campaign_conversions.table.medium'))->sortable()->searchable()->limit(20), TextColumn::make('device_type')->label(__('campaign_conversions.table.device_type'))->badge()->color(fn(?string $state): string => match ($state) {
            'mobile' => 'info',
            'tablet' => 'warning',
            'desktop' => 'success',
            default => 'gray',
        })->formatStateUsing(fn(?string $state): string => $state ? __("campaign_conversions.device_types.{$state}") : '-'), TextColumn::make('country')->label(__('campaign_conversions.table.country'))->sortable()->searchable()->limit(15), TextColumn::make('roi')->label(__('campaign_conversions.table.roi'))->formatStateUsing(fn(?float $state): string => $state ? number_format($state * 100, 2) . '%' : '-')->color(fn(?float $state): string => $state && $state > 0 ? 'success' : ($state && $state < 0 ? 'danger' : 'gray'))->sortable(), TextColumn::make('converted_at')->label(__('campaign_conversions.table.converted_at'))->dateTime()->sortable()->since()])->filters([SelectFilter::make('campaign_id')->label(__('campaign_conversions.filters.campaign'))->relationship('campaign', 'name')->searchable()->preload(), SelectFilter::make('conversion_type')->label(__('campaign_conversions.filters.conversion_type'))->options(['purchase' => __('campaign_conversions.conversion_types.purchase'), 'signup' => __('campaign_conversions.conversion_types.signup'), 'download' => __('campaign_conversions.conversion_types.download'), 'lead' => __('campaign_conversions.conversion_types.lead'), 'subscription' => __('campaign_conversions.conversion_types.subscription'), 'trial' => __('campaign_conversions.conversion_types.trial'), 'custom' => __('campaign_conversions.conversion_types.custom')]), SelectFilter::make('status')->label(__('campaign_conversions.filters.status'))->options(['pending' => __('campaign_conversions.statuses.pending'), 'completed' => __('campaign_conversions.statuses.completed'), 'cancelled' => __('campaign_conversions.statuses.cancelled'), 'refunded' => __('campaign_conversions.statuses.refunded')]), SelectFilter::make('device_type')->label(__('campaign_conversions.filters.device_type'))->options(['mobile' => __('campaign_conversions.device_types.mobile'), 'tablet' => __('campaign_conversions.device_types.tablet'), 'desktop' => __('campaign_conversions.device_types.desktop')]), SelectFilter::make('source')->label(__('campaign_conversions.filters.source'))->options(fn(): array => CampaignConversion::distinct()->pluck('source', 'source')->filter()->toArray()), SelectFilter::make('medium')->label(__('campaign_conversions.filters.medium'))->options(fn(): array => CampaignConversion::distinct()->pluck('medium', 'medium')->filter()->toArray()), DateFilter::make('converted_at')->label(__('campaign_conversions.filters.converted_at')), Filter::make('high_value')->label(__('campaign_conversions.filters.high_value'))->query(fn(Builder $query): Builder => $query->where('conversion_value', '>=', 100)), Filter::make('recent')->label(__('campaign_conversions.filters.recent'))->query(fn(Builder $query): Builder => $query->where('converted_at', '>=', now()->subDays(7))), TernaryFilter::make('is_mobile')->label(__('campaign_conversions.filters.mobile')), TernaryFilter::make('is_tablet')->label(__('campaign_conversions.filters.tablet')), TernaryFilter::make('is_desktop')->label(__('campaign_conversions.filters.desktop'))])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make(), Action::make('calculate_roi')->label(__('campaign_conversions.actions.calculate_roi'))->icon('heroicon-o-calculator')->color('info')->form([TextInput::make('cost')->label(__('campaign_conversions.form.cost'))->numeric()->prefix('€')->required()])->action(function (CampaignConversion $record, array $data): void {
            $roi = $record->calculateRoi((float) $data['cost']);
            $record->update(['roi' => $roi]);
        })])->bulkActions([BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make(), BulkAction::make('mark_completed')->label(__('campaign_conversions.actions.mark_completed'))->icon('heroicon-o-check-circle')->color('success')->action(fn(Collection $records) => $records->each->update(['status' => 'completed'])), BulkAction::make('export')->label(__('campaign_conversions.actions.export'))->icon('heroicon-o-arrow-down-tray')->color('info')->action(function (Collection $records) {
            // Export logic here
        })])])->defaultSort('converted_at', 'desc');
    }
    /**
     * Handle infolist functionality with proper error handling.
     * @param Schema $schema
     * @return Schema
     */
    public static function infolist(Schema $schema): Schema
    {
        return $infolist->schema([InfolistSection::make(__('campaign_conversions.infolist.basic_information'))->schema([TextEntry::make('id')->label(__('campaign_conversions.infolist.id')), TextEntry::make('campaign.name')->label(__('campaign_conversions.infolist.campaign')), TextEntry::make('conversion_type')->label(__('campaign_conversions.infolist.conversion_type'))->badge()->color(fn(string $state): string => match ($state) {
            'purchase' => 'success',
            'signup' => 'info',
            'download' => 'warning',
            'lead' => 'primary',
            default => 'gray',
        })->formatStateUsing(fn(string $state): string => __("campaign_conversions.conversion_types.{$state}")), TextEntry::make('conversion_value')->label(__('campaign_conversions.infolist.conversion_value'))->money('EUR')->weight(FontWeight::Bold), TextEntry::make('status')->label(__('campaign_conversions.infolist.status'))->badge()->colors(['warning' => 'pending', 'success' => 'completed', 'danger' => 'cancelled', 'secondary' => 'refunded'])->formatStateUsing(fn(string $state): string => __("campaign_conversions.statuses.{$state}")), TextEntry::make('converted_at')->label(__('campaign_conversions.infolist.converted_at'))->dateTime()])->columns(3), InfolistSection::make(__('campaign_conversions.infolist.customer_information'))->schema([TextEntry::make('customer.email')->label(__('campaign_conversions.infolist.customer_email')), TextEntry::make('customer.name')->label(__('campaign_conversions.infolist.customer_name')), TextEntry::make('order.id')->label(__('campaign_conversions.infolist.order_id')), TextEntry::make('session_id')->label(__('campaign_conversions.infolist.session_id'))])->columns(2), InfolistSection::make(__('campaign_conversions.infolist.tracking_information'))->schema([TextEntry::make('source')->label(__('campaign_conversions.infolist.source')), TextEntry::make('medium')->label(__('campaign_conversions.infolist.medium')), TextEntry::make('campaign_name')->label(__('campaign_conversions.infolist.campaign_name')), TextEntry::make('utm_content')->label(__('campaign_conversions.infolist.utm_content')), TextEntry::make('utm_term')->label(__('campaign_conversions.infolist.utm_term')), TextEntry::make('referrer')->label(__('campaign_conversions.infolist.referrer'))->url()])->columns(3), InfolistSection::make(__('campaign_conversions.infolist.device_information'))->schema([TextEntry::make('device_type')->label(__('campaign_conversions.infolist.device_type'))->badge()->color(fn(?string $state): string => match ($state) {
            'mobile' => 'info',
            'tablet' => 'warning',
            'desktop' => 'success',
            default => 'gray',
        })->formatStateUsing(fn(?string $state): string => $state ? __("campaign_conversions.device_types.{$state}") : '-'), TextEntry::make('browser')->label(__('campaign_conversions.infolist.browser')), TextEntry::make('os')->label(__('campaign_conversions.infolist.os')), TextEntry::make('country')->label(__('campaign_conversions.infolist.country')), TextEntry::make('city')->label(__('campaign_conversions.infolist.city')), TextEntry::make('ip_address')->label(__('campaign_conversions.infolist.ip_address'))])->columns(3), InfolistSection::make(__('campaign_conversions.infolist.performance_metrics'))->schema([TextEntry::make('roi')->label(__('campaign_conversions.infolist.roi'))->formatStateUsing(fn(?float $state): string => $state ? number_format($state * 100, 2) . '%' : '-')->color(fn(?float $state): string => $state && $state > 0 ? 'success' : ($state && $state < 0 ? 'danger' : 'gray')), TextEntry::make('roas')->label(__('campaign_conversions.infolist.roas'))->formatStateUsing(fn(?float $state): string => $state ? number_format($state * 100, 2) . '%' : '-'), TextEntry::make('cost_per_conversion')->label(__('campaign_conversions.infolist.cost_per_conversion'))->money('EUR'), TextEntry::make('lifetime_value')->label(__('campaign_conversions.infolist.lifetime_value'))->money('EUR'), TextEntry::make('customer_acquisition_cost')->label(__('campaign_conversions.infolist.customer_acquisition_cost'))->money('EUR'), TextEntry::make('conversion_rate')->label(__('campaign_conversions.infolist.conversion_rate'))->formatStateUsing(fn(?float $state): string => $state ? number_format($state * 100, 2) . '%' : '-')])->columns(3), InfolistSection::make(__('campaign_conversions.infolist.additional_information'))->schema([TextEntry::make('notes')->label(__('campaign_conversions.infolist.notes'))->columnSpanFull(), KeyValueEntry::make('custom_attributes')->label(__('campaign_conversions.infolist.custom_attributes'))->columnSpanFull(), KeyValueEntry::make('conversion_data')->label(__('campaign_conversions.infolist.conversion_data'))->columnSpanFull()])]);
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
        return ['index' => Pages\ListCampaignConversions::route('/'), 'create' => Pages\CreateCampaignConversion::route('/create'), 'view' => Pages\ViewCampaignConversion::route('/{record}'), 'edit' => Pages\EditCampaignConversion::route('/{record}/edit')];
    }
    /**
     * Handle getNavigationBadge functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    /**
     * Handle getNavigationBadgeColor functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}