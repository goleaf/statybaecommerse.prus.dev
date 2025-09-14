<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use BackedEnum;
use App\Filament\Resources\ReferralResource\Pages;
use App\Filament\Resources\ReferralResource\Widgets;
use App\Models\Referral;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
/**
 * ReferralResource
 * 
 * Filament v4 resource for ReferralResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
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
final class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;
    /**
     * @var BackedEnum|string|null
     */
    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-users';
    /** @var BackedEnum|string|null */
    /**
     * @var UnitEnum|string|null
     */
    /** @var UnitEnum|string|null */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Referral;
    protected static ?string $navigationLabel = null;
    protected static ?string $modelLabel = null;
    protected static ?string $pluralModelLabel = null;
    protected static ?int $navigationSort = 1;
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('referrals.navigation_label');
    }
    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('referrals.model_label');
    }
    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('referrals.plural_model_label');
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Forms\Components\Section::make(__('referrals.referral_information'))->schema([Forms\Components\Select::make('referrer_id')->label(__('referrals.referrer'))->relationship('referrer', 'name')->searchable()->preload()->required()->createOptionForm([Forms\Components\TextInput::make('name')->required()->maxLength(255), Forms\Components\TextInput::make('email')->email()->required()->maxLength(255)]), Forms\Components\Select::make('referred_id')->label(__('referrals.referred_user'))->relationship('referred', 'name')->searchable()->preload()->required()->createOptionForm([Forms\Components\TextInput::make('name')->required()->maxLength(255), Forms\Components\TextInput::make('email')->email()->required()->maxLength(255)]), Forms\Components\TextInput::make('referral_code')->label(__('referrals.referral_code'))->required()->maxLength(20)->unique(ignoreRecord: true)->default(fn() => strtoupper(substr(md5(uniqid()), 0, 8)))->helperText(__('referrals.referral_code_help')), Forms\Components\Select::make('status')->label(__('referrals.status'))->options(['pending' => __('referrals.status_pending'), 'completed' => __('referrals.status_completed'), 'expired' => __('referrals.status_expired')])->required()->default('pending'), Forms\Components\DateTimePicker::make('expires_at')->label(__('referrals.expires_at'))->nullable()->helperText(__('referrals.expires_at_help')), Forms\Components\DateTimePicker::make('completed_at')->label(__('referrals.completed_at'))->nullable()->disabled(fn(Forms\Get $get) => $get('status') !== 'completed')])->columns(2), Forms\Components\Section::make(__('referrals.translation_information'))->schema([Forms\Components\TextInput::make('title')->label(__('referrals.title'))->maxLength(255)->translatable(), Forms\Components\Textarea::make('description')->label(__('referrals.description'))->rows(3)->translatable(), Forms\Components\Textarea::make('terms_conditions')->label(__('referrals.terms_conditions'))->rows(4)->translatable(), Forms\Components\Textarea::make('benefits_description')->label(__('referrals.benefits_description'))->rows(3)->translatable(), Forms\Components\Textarea::make('how_it_works')->label(__('referrals.how_it_works'))->rows(3)->translatable()])->columns(1)->collapsible(), Forms\Components\Section::make(__('referrals.seo_information'))->schema([Forms\Components\TextInput::make('seo_title')->label(__('referrals.seo_title'))->maxLength(255)->translatable(), Forms\Components\Textarea::make('seo_description')->label(__('referrals.seo_description'))->rows(3)->translatable(), Forms\Components\TagsInput::make('seo_keywords')->label(__('referrals.seo_keywords'))->translatable()])->columns(1)->collapsible(), Forms\Components\Section::make(__('referrals.metadata'))->schema([Forms\Components\KeyValue::make('metadata')->label(__('referrals.additional_data'))->nullable()->helperText(__('referrals.metadata_help'))])->collapsible()]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('referrer.name')->label(__('referrals.referrer'))->searchable()->sortable()->url(fn(Referral $record): string => route('filament.admin.resources.users.view', $record->referrer_id))->icon('heroicon-o-user'), Tables\Columns\TextColumn::make('referred.name')->label(__('referrals.referred_user'))->searchable()->sortable()->url(fn(Referral $record): string => route('filament.admin.resources.users.view', $record->referred_id))->icon('heroicon-o-user'), Tables\Columns\TextColumn::make('referral_code')->label(__('referrals.referral_code'))->searchable()->copyable()->sortable()->badge()->color('info'), Tables\Columns\TextColumn::make('status')->label(__('referrals.status'))->badge()->color(fn(string $state): string => match ($state) {
            'pending' => 'warning',
            'completed' => 'success',
            'expired' => 'danger',
        })->sortable(), Tables\Columns\TextColumn::make('created_at')->label(__('referrals.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('completed_at')->label(__('referrals.completed_at'))->dateTime()->sortable()->toggleable()->placeholder(__('referrals.not_completed')), Tables\Columns\TextColumn::make('expires_at')->label(__('referrals.expires_at'))->dateTime()->sortable()->toggleable()->placeholder(__('referrals.never_expires')), Tables\Columns\TextColumn::make('rewards_count')->label(__('referrals.rewards'))->counts('rewards')->sortable()->badge()->color('success'), Tables\Columns\TextColumn::make('total_rewards_amount')->label(__('referrals.total_rewards_amount'))->getStateUsing(fn(Referral $record): string => number_format($record->rewards()->sum('amount'), 2) . ' €')->sortable()->toggleable(), Tables\Columns\TextColumn::make('days_since_created')->label(__('referrals.days_since_created'))->getStateUsing(fn(Referral $record): int => $record->created_at->diffInDays(now()))->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('title')->label(__('referrals.title'))->searchable()->sortable()->toggleable()->limit(30), Tables\Columns\TextColumn::make('source')->label(__('referrals.source'))->searchable()->sortable()->toggleable()->badge()->color('info'), Tables\Columns\TextColumn::make('campaign')->label(__('referrals.campaign'))->searchable()->sortable()->toggleable()->badge()->color('warning'), Tables\Columns\TextColumn::make('conversion_rate')->label(__('referrals.conversion_rate'))->getStateUsing(fn(Referral $record): string => $record->conversion_rate . '%')->sortable()->toggleable()->badge()->color(fn(string $state): string => match (true) {
            (float) $state >= 50 => 'success',
            (float) $state >= 25 => 'warning',
            default => 'danger',
        }), Tables\Columns\TextColumn::make('performance_score')->label(__('referrals.performance_score'))->getStateUsing(fn(Referral $record): int => $record->performance_score)->sortable()->toggleable()->badge()->color(fn(int $state): string => match (true) {
            $state >= 80 => 'success',
            $state >= 60 => 'warning',
            default => 'danger',
        }), Tables\Columns\IconColumn::make('is_about_to_expire')->label(__('referrals.about_to_expire'))->getStateUsing(fn(Referral $record): bool => $record->isAboutToExpire())->boolean()->toggleable(isToggledHiddenByDefault: true)->color('warning')])->filters([SelectFilter::make('status')->label(__('referrals.status'))->options(['pending' => __('referrals.status_pending'), 'completed' => __('referrals.status_completed'), 'expired' => __('referrals.status_expired')]), Filter::make('expired')->label(__('referrals.filter_expired'))->query(fn(Builder $query): Builder => $query->expired()), Filter::make('active')->label(__('referrals.filter_active'))->query(fn(Builder $query): Builder => $query->active()), Filter::make('completed')->label(__('referrals.filter_completed'))->query(fn(Builder $query): Builder => $query->completed()), DateFilter::make('created_at')->label(__('referrals.created_at')), DateFilter::make('completed_at')->label(__('referrals.completed_at')), TernaryFilter::make('has_rewards')->label(__('referrals.has_rewards'))->queries(true: fn(Builder $query) => $query->has('rewards'), false: fn(Builder $query) => $query->doesntHave('rewards')), SelectFilter::make('source')->label(__('referrals.source'))->options(fn(): array => Referral::distinct()->pluck('source')->filter()->mapWithKeys(fn($source) => [$source => $source])->toArray()), SelectFilter::make('campaign')->label(__('referrals.campaign'))->options(fn(): array => Referral::distinct()->pluck('campaign')->filter()->mapWithKeys(fn($campaign) => [$campaign => $campaign])->toArray()), Filter::make('about_to_expire')->label(__('referrals.about_to_expire'))->query(fn(Builder $query): Builder => $query->where('expires_at', '<=', now()->addDays(7))->where('expires_at', '>', now())->where('status', 'pending')), Filter::make('high_performance')->label(__('referrals.high_performance'))->query(fn(Builder $query): Builder => $query->where('status', 'completed')->has('rewards')), Tables\Filters\TrashedFilter::make()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\Action::make('mark_completed')->label(__('referrals.mark_completed'))->icon('heroicon-o-check-circle')->color('success')->visible(fn(Referral $record): bool => $record->status === 'pending')->action(function (Referral $record): void {
            $record->markAsCompleted();
            Notification::make()->title(__('referrals.referral_updated'))->success()->send();
        })->requiresConfirmation(), Tables\Actions\Action::make('mark_expired')->label(__('referrals.mark_expired'))->icon('heroicon-o-x-circle')->color('danger')->visible(fn(Referral $record): bool => $record->status === 'pending')->action(function (Referral $record): void {
            $record->markAsExpired();
            Notification::make()->title(__('referrals.referral_updated'))->success()->send();
        })->requiresConfirmation(), Tables\Actions\DeleteAction::make(), Tables\Actions\RestoreAction::make(), Tables\Actions\ForceDeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\BulkAction::make('mark_completed')->label(__('referrals.mark_completed'))->icon('heroicon-o-check-circle')->color('success')->action(function ($records): void {
            $records->each->markAsCompleted();
            Notification::make()->title(__('referrals.referrals_updated'))->success()->send();
        })->requiresConfirmation(), Tables\Actions\BulkAction::make('mark_expired')->label(__('referrals.mark_expired'))->icon('heroicon-o-x-circle')->color('danger')->action(function ($records): void {
            $records->each->markAsExpired();
            Notification::make()->title(__('referrals.referrals_updated'))->success()->send();
        })->requiresConfirmation(), Tables\Actions\DeleteBulkAction::make(), Tables\Actions\RestoreBulkAction::make(), Tables\Actions\ForceDeleteBulkAction::make()])])->defaultSort('created_at', 'desc')->poll('30s');
    }
    /**
     * Handle infolist functionality with proper error handling.
     * @param Schema $form
     * @return Schema
     */
    public static function infolist(Schema $form): Schema
    {
        return $schema->components([Infolists\Components\Section::make(__('referrals.referral_details'))->components([Infolists\Components\TextEntry::make('referrer.name')->label(__('referrals.referrer'))->url(fn(Referral $record): string => route('filament.admin.resources.users.view', $record->referrer_id)), Infolists\Components\TextEntry::make('referred.name')->label(__('referrals.referred_user'))->url(fn(Referral $record): string => route('filament.admin.resources.users.view', $record->referred_id)), Infolists\Components\TextEntry::make('referral_code')->label(__('referrals.referral_code'))->copyable()->badge()->color('info'), Infolists\Components\TextEntry::make('status')->label(__('referrals.status'))->badge()->color(fn(string $state): string => match ($state) {
            'pending' => 'warning',
            'completed' => 'success',
            'expired' => 'danger',
        }), Infolists\Components\TextEntry::make('created_at')->label(__('referrals.created_at'))->dateTime(), Infolists\Components\TextEntry::make('completed_at')->label(__('referrals.completed_at'))->dateTime()->placeholder(__('referrals.not_completed')), Infolists\Components\TextEntry::make('expires_at')->label(__('referrals.expires_at'))->dateTime()->placeholder(__('referrals.never_expires')), Infolists\Components\TextEntry::make('days_since_created')->label(__('referrals.days_since_created'))->getStateUsing(fn(Referral $record): int => $record->created_at->diffInDays(now()))])->columns(2), Infolists\Components\Section::make(__('referrals.rewards_section'))->components([Infolists\Components\RepeatableEntry::make('rewards')->components([Infolists\Components\TextEntry::make('type')->label(__('referrals.type'))->badge()->color(fn(string $state): string => match ($state) {
            'referrer_bonus' => 'success',
            'referred_discount' => 'info',
            default => 'gray',
        }), Infolists\Components\TextEntry::make('amount')->label(__('referrals.amount'))->money('EUR'), Infolists\Components\TextEntry::make('status')->label(__('referrals.status'))->badge()->color(fn(string $state): string => match ($state) {
            'pending' => 'warning',
            'applied' => 'success',
            'expired' => 'danger',
        }), Infolists\Components\TextEntry::make('applied_at')->label(__('referrals.applied_at'))->dateTime()->placeholder(__('referrals.not_applied'))])->columns(4)])->collapsible(), Infolists\Components\Section::make(__('referrals.metadata'))->components([Infolists\Components\KeyValueEntry::make('metadata')->label(__('referrals.additional_data'))])->collapsible()->collapsed()]);
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
        return ['index' => Pages\ListReferrals::route('/'), 'create' => Pages\CreateReferral::route('/create'), 'view' => Pages\ViewReferral::route('/{record}'), 'edit' => Pages\EditReferral::route('/{record}/edit')];
    }
    /**
     * Handle getWidgets functionality with proper error handling.
     * @return array
     */
    public static function getWidgets(): array
    {
        return [Widgets\ReferralStatsWidget::class, Widgets\TopReferrersWidget::class, Widgets\RecentReferralsWidget::class, Widgets\ReferralTrendsWidget::class];
    }
    /**
     * Handle getEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class])->with(['referrer', 'referred', 'rewards']);
    }
    /**
     * Handle getGlobalSearchResultTitle functionality with proper error handling.
     * @param mixed $record
     * @return string
     */
    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->referral_code . ' - ' . $record->referrer->name . ' → ' . $record->referred->name;
    }
    /**
     * Handle getGlobalSearchResultDetails functionality with proper error handling.
     * @param mixed $record
     * @return array
     */
    public static function getGlobalSearchResultDetails($record): array
    {
        return [__('referrals.referrer') => $record->referrer->name, __('referrals.referred_user') => $record->referred->name, __('referrals.status') => $record->status, __('referrals.created_at') => $record->created_at->format('Y-m-d H:i')];
    }
    /**
     * Handle getGlobalSearchResultUrl functionality with proper error handling.
     * @param mixed $record
     * @return string
     */
    public static function getGlobalSearchResultUrl($record): string
    {
        return route('filament.admin.resources.referrals.view', $record);
    }
}