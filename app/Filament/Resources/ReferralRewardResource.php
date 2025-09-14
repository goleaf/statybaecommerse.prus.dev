<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ReferralRewardResource\Pages;
use App\Models\ReferralReward;
use BackedEnum;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
/**
 * ReferralRewardResource
 * 
 * Filament v4 resource for ReferralRewardResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property mixed $navigationGroup
 * @property string|null $navigationLabel
 * @property string|null $modelLabel
 * @property string|null $pluralModelLabel
 * @property int|null $navigationSort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ReferralRewardResource extends Resource
{
    protected static ?string $model = ReferralReward::class;
    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-gift';
    /**
     * @var UnitEnum|string|null
     */
    /** @var UnitEnum|string|null */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Referral;
    protected static ?string $navigationLabel = 'referrals.navigation.rewards';
    protected static ?string $modelLabel = 'referrals.forms.reward';
    protected static ?string $pluralModelLabel = 'referrals.navigation.rewards';
    protected static ?int $navigationSort = 2;
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([Forms\Components\Section::make(__('referrals.forms.reward_information'))->components([Forms\Components\Select::make('referral_id')->label(__('referrals.forms.referral'))->relationship('referral', 'referral_code')->searchable()->preload()->required(), Forms\Components\Select::make('user_id')->label(__('referrals.forms.user'))->relationship('user', 'name')->searchable()->preload()->required(), Forms\Components\Select::make('order_id')->label(__('referrals.forms.order'))->relationship('order', 'id')->searchable()->preload()->nullable(), Forms\Components\Select::make('type')->label(__('referrals.forms.type'))->options(['referrer_bonus' => __('referrals.types.referrer_bonus'), 'referred_discount' => __('referrals.types.referred_discount')])->required(), Forms\Components\TextInput::make('title')->label(__('referrals.forms.title'))->required()->maxLength(255)->translatable(), Forms\Components\Textarea::make('description')->label(__('referrals.forms.description'))->nullable()->translatable()->rows(3), Forms\Components\TextInput::make('amount')->label(__('referrals.forms.amount'))->numeric()->required()->prefix('€'), Forms\Components\TextInput::make('currency_code')->label(__('referrals.forms.currency'))->default('EUR')->required()->maxLength(3), Forms\Components\Select::make('status')->label(__('referrals.forms.status'))->options(['pending' => __('referrals.status.pending'), 'applied' => __('referrals.status.applied'), 'expired' => __('referrals.status.expired')])->required()->default('pending'), Forms\Components\Toggle::make('is_active')->label(__('referrals.forms.active'))->default(true), Forms\Components\TextInput::make('priority')->label(__('referrals.forms.priority'))->numeric()->default(0)->minValue(0)->maxValue(100), Forms\Components\DateTimePicker::make('applied_at')->label(__('referrals.forms.applied_at'))->nullable()->disabled(fn(Forms\Get $get) => $get('status') !== 'applied'), Forms\Components\DateTimePicker::make('expires_at')->label(__('referrals.forms.expires_at'))->nullable()])->columns(2), Forms\Components\Section::make(__('referrals.forms.conditions'))->components([Forms\Components\Repeater::make('conditions')->label(__('referrals.forms.conditions'))->schema([Forms\Components\TextInput::make('field')->label(__('referrals.forms.condition_field'))->required(), Forms\Components\Select::make('operator')->label(__('referrals.forms.condition_operator'))->options(['=' => 'Equals', '!=' => 'Not Equals', '>' => 'Greater Than', '>=' => 'Greater Than or Equal', '<' => 'Less Than', '<=' => 'Less Than or Equal', 'in' => 'In', 'not_in' => 'Not In'])->required(), Forms\Components\TextInput::make('value')->label(__('referrals.forms.condition_value'))->required()])->columns(3)->nullable()])->collapsible(), Forms\Components\Section::make(__('referrals.forms.metadata'))->components([Forms\Components\KeyValue::make('metadata')->label(__('referrals.forms.additional_data'))->nullable(), Forms\Components\KeyValue::make('reward_data')->label(__('referrals.forms.reward_data'))->nullable()])->collapsible()]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('title')->label(__('referrals.forms.title'))->searchable()->sortable()->limit(30), Tables\Columns\TextColumn::make('user.name')->label(__('referrals.forms.user'))->searchable()->sortable(), Tables\Columns\TextColumn::make('referral.referral_code')->label(__('referrals.forms.referral_code'))->searchable()->sortable(), Tables\Columns\TextColumn::make('order.id')->label(__('referrals.forms.order'))->searchable()->sortable()->placeholder('N/A'), Tables\Columns\TextColumn::make('type')->label(__('referrals.forms.type'))->badge()->color(fn(string $state): string => match ($state) {
            'referrer_bonus' => 'success',
            'referred_discount' => 'info',
        })->formatStateUsing(fn(string $state): string => match ($state) {
            'referrer_bonus' => __('referrals.types.referrer_bonus'),
            'referred_discount' => __('referrals.types.referred_discount'),
        })->sortable(), Tables\Columns\TextColumn::make('amount')->label(__('referrals.forms.amount'))->money('EUR')->sortable(), Tables\Columns\TextColumn::make('status')->label(__('referrals.forms.status'))->badge()->color(fn(string $state): string => match ($state) {
            'pending' => 'warning',
            'applied' => 'success',
            'expired' => 'danger',
        })->formatStateUsing(fn(string $state): string => match ($state) {
            'pending' => __('referrals.status.pending'),
            'applied' => __('referrals.status.applied'),
            'expired' => __('referrals.status.expired'),
        })->sortable(), Tables\Columns\IconColumn::make('is_active')->label(__('referrals.forms.active'))->boolean()->sortable(), Tables\Columns\TextColumn::make('priority')->label(__('referrals.forms.priority'))->sortable()->toggleable(), Tables\Columns\TextColumn::make('created_at')->label(__('referrals.forms.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('applied_at')->label(__('referrals.forms.applied_at'))->dateTime()->sortable()->toggleable(), Tables\Columns\TextColumn::make('expires_at')->label(__('referrals.forms.expires_at'))->dateTime()->sortable()->toggleable()])->filters([SelectFilter::make('type')->label(__('referrals.forms.type'))->options(['referrer_bonus' => __('referrals.types.referrer_bonus'), 'referred_discount' => __('referrals.types.referred_discount')]), SelectFilter::make('status')->label(__('referrals.forms.status'))->options(['pending' => __('referrals.status.pending'), 'applied' => __('referrals.status.applied'), 'expired' => __('referrals.status.expired')]), SelectFilter::make('is_active')->label(__('referrals.forms.active'))->options(['1' => __('referrals.filters.active'), '0' => __('referrals.filters.inactive')]), Filter::make('pending')->label(__('referrals.filters.pending'))->query(fn(Builder $query): Builder => $query->pending()), Filter::make('applied')->label(__('referrals.filters.applied'))->query(fn(Builder $query): Builder => $query->applied()), Filter::make('expired')->label(__('referrals.filters.expired'))->query(fn(Builder $query): Builder => $query->expired()), Filter::make('referrer_bonus')->label(__('referrals.filters.referrer_bonus'))->query(fn(Builder $query): Builder => $query->referrerBonus()), Filter::make('referred_discount')->label(__('referrals.filters.referred_discount'))->query(fn(Builder $query): Builder => $query->referredDiscount()), Filter::make('active')->label(__('referrals.filters.active'))->query(fn(Builder $query): Builder => $query->active()), Tables\Filters\TrashedFilter::make()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\Action::make('apply')->label(__('referrals.actions.apply_reward'))->icon('heroicon-o-check-circle')->color('success')->visible(fn(ReferralReward $record): bool => $record->status === 'pending')->requiresConfirmation()->action(function (ReferralReward $record): void {
            $record->apply();
            $record->logAction('applied', ['applied_by' => auth()->id()]);
        }), Tables\Actions\Action::make('expire')->label(__('referrals.actions.mark_expired'))->icon('heroicon-o-x-circle')->color('danger')->visible(fn(ReferralReward $record): bool => $record->status === 'pending')->requiresConfirmation()->action(function (ReferralReward $record): void {
            $record->markAsExpired();
            $record->logAction('expired', ['expired_by' => auth()->id()]);
        }), Tables\Actions\DeleteAction::make(), Tables\Actions\RestoreAction::make(), Tables\Actions\ForceDeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\BulkAction::make('apply_selected')->label(__('referrals.actions.apply_selected'))->icon('heroicon-o-check-circle')->color('success')->requiresConfirmation()->action(function (Collection $records): void {
            $records->each(function (ReferralReward $record) {
                if ($record->status === 'pending') {
                    $record->apply();
                    $record->logAction('bulk_applied', ['applied_by' => auth()->id()]);
                }
            });
        }), Tables\Actions\BulkAction::make('expire_selected')->label(__('referrals.actions.expire_selected'))->icon('heroicon-o-x-circle')->color('danger')->requiresConfirmation()->action(function (Collection $records): void {
            $records->each(function (ReferralReward $record) {
                if ($record->status === 'pending') {
                    $record->markAsExpired();
                    $record->logAction('bulk_expired', ['expired_by' => auth()->id()]);
                }
            });
        }), Tables\Actions\DeleteBulkAction::make(), Tables\Actions\RestoreBulkAction::make(), Tables\Actions\ForceDeleteBulkAction::make()])])->defaultSort('created_at', 'desc');
    }
    /**
     * Handle infolist functionality with proper error handling.
     * @param Schema $form
     * @return Schema
     */
    public static function infolist(Schema $form): Schema
    {
        return $schema->components([Infolists\Components\Section::make(__('referrals.forms.reward_details'))->components([Infolists\Components\TextEntry::make('title')->label(__('referrals.forms.title'))->translatable(), Infolists\Components\TextEntry::make('description')->label(__('referrals.forms.description'))->translatable()->placeholder(__('referrals.forms.no_description')), Infolists\Components\TextEntry::make('user.name')->label(__('referrals.forms.user')), Infolists\Components\TextEntry::make('referral.referral_code')->label(__('referrals.forms.referral_code')), Infolists\Components\TextEntry::make('order.id')->label(__('referrals.forms.order'))->placeholder('N/A'), Infolists\Components\TextEntry::make('type')->label(__('referrals.forms.type'))->badge()->color(fn(string $state): string => match ($state) {
            'referrer_bonus' => 'success',
            'referred_discount' => 'info',
        })->formatStateUsing(fn(string $state): string => match ($state) {
            'referrer_bonus' => __('referrals.types.referrer_bonus'),
            'referred_discount' => __('referrals.types.referred_discount'),
        }), Infolists\Components\TextEntry::make('amount')->label(__('referrals.forms.amount'))->money('EUR'), Infolists\Components\TextEntry::make('status')->label(__('referrals.forms.status'))->badge()->color(fn(string $state): string => match ($state) {
            'pending' => 'warning',
            'applied' => 'success',
            'expired' => 'danger',
        })->formatStateUsing(fn(string $state): string => match ($state) {
            'pending' => __('referrals.status.pending'),
            'applied' => __('referrals.status.applied'),
            'expired' => __('referrals.status.expired'),
        }), Infolists\Components\IconEntry::make('is_active')->label(__('referrals.forms.active'))->boolean(), Infolists\Components\TextEntry::make('priority')->label(__('referrals.forms.priority')), Infolists\Components\TextEntry::make('created_at')->label(__('referrals.forms.created_at'))->dateTime(), Infolists\Components\TextEntry::make('applied_at')->label(__('referrals.forms.applied_at'))->dateTime()->placeholder(__('referrals.forms.not_applied')), Infolists\Components\TextEntry::make('expires_at')->label(__('referrals.forms.expires_at'))->dateTime()->placeholder(__('referrals.forms.never_expires'))])->columns(2), Infolists\Components\Section::make(__('referrals.forms.conditions'))->components([Infolists\Components\TextEntry::make('conditions')->label(__('referrals.forms.conditions'))->formatStateUsing(fn($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : __('referrals.forms.no_conditions'))->placeholder(__('referrals.forms.no_conditions'))])->collapsible(), Infolists\Components\Section::make(__('referrals.forms.metadata'))->components([Infolists\Components\TextEntry::make('metadata')->label(__('referrals.forms.additional_data'))->formatStateUsing(fn($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : __('referrals.forms.no_metadata'))->placeholder(__('referrals.forms.no_metadata')), Infolists\Components\TextEntry::make('reward_data')->label(__('referrals.forms.reward_data'))->formatStateUsing(fn($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : __('referrals.forms.no_reward_data'))->placeholder(__('referrals.forms.no_reward_data'))])->collapsible()]);
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
     * Handle getWidgets functionality with proper error handling.
     * @return array
     */
    public static function getWidgets(): array
    {
        return [Widgets\ReferralRewardStatsWidget::class, Widgets\ReferralRewardChartWidget::class, Widgets\ReferralRewardTypeChartWidget::class];
    }
    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return ['index' => Pages\ListReferralRewards::route('/'), 'create' => Pages\CreateReferralReward::route('/create'), 'view' => Pages\ViewReferralReward::route('/{record}'), 'edit' => Pages\EditReferralReward::route('/{record}/edit')];
    }
    /**
     * Handle getEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([\Illuminate\Database\Eloquent\SoftDeletingScope::class]);
    }
}