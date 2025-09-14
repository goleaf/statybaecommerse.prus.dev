<?php

declare (strict_types=1);
namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Filament\Resources\ActivityLogResource\RelationManagers;
use App\Filament\Resources\ActivityLogResource\Widgets;
use BackedEnum;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;
/**
 * ActivityLogResource
 * 
 * Filament v4 resource for ActivityLogResource management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $model
 * @property mixed $navigationIcon
 * @property string|null $recordTitleAttribute
 * @property string|null $navigationLabel
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;
    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.system');
    }
    protected static ?string $recordTitleAttribute = 'description';
    protected static ?string $navigationLabel = null;
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.activity_logs.title');
    }
    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.models.activity_log');
    }
    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.models.activity_logs');
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([Forms\Components\Section::make(__('admin.activity_logs.sections.activity_information'))->schema([Forms\Components\TextInput::make('log_name')->label(__('admin.activity_logs.fields.log_name'))->maxLength(255)->required(), Forms\Components\TextInput::make('description')->label(__('admin.activity_logs.fields.description'))->maxLength(255)->required()->columnSpanFull(), Forms\Components\Select::make('subject_type')->label(__('admin.activity_logs.fields.subject_type'))->options(['App\Models\User' => __('admin.activity_logs.subject_types.App\Models\User'), 'App\Models\Product' => __('admin.activity_logs.subject_types.App\Models\Product'), 'App\Models\Order' => __('admin.activity_logs.subject_types.App\Models\Order'), 'App\Models\Category' => __('admin.activity_logs.subject_types.App\Models\Category'), 'App\Models\Brand' => __('admin.activity_logs.subject_types.App\Models\Brand'), 'App\Models\Collection' => __('admin.activity_logs.subject_types.App\Models\Collection'), 'App\Models\Review' => __('admin.activity_logs.subject_types.App\Models\Review'), 'App\Models\Discount' => __('admin.activity_logs.subject_types.App\Models\Discount'), 'App\Models\Coupon' => __('admin.activity_logs.subject_types.App\Models\Coupon'), 'App\Models\Campaign' => __('admin.activity_logs.subject_types.App\Models\Campaign'), 'App\Models\Media' => __('admin.activity_logs.subject_types.App\Models\Media'), 'App\Models\CartItem' => __('admin.activity_logs.subject_types.App\Models\CartItem'), 'App\Models\CustomerGroup' => __('admin.activity_logs.subject_types.App\Models\CustomerGroup'), 'App\Models\LegalPage' => __('admin.activity_logs.subject_types.App\Models\LegalPage'), 'App\Models\Address' => __('admin.activity_logs.subject_types.App\Models\Address'), 'App\Models\Inventory' => __('admin.activity_logs.subject_types.App\Models\Inventory'), 'App\Models\Backup' => __('admin.activity_logs.subject_types.App\Models\Backup'), 'App\Models\Currency' => __('admin.activity_logs.subject_types.App\Models\Currency')])->searchable(), Forms\Components\TextInput::make('subject_id')->label(__('admin.activity_logs.fields.subject_id'))->numeric(), Forms\Components\Select::make('causer_type')->label(__('admin.activity_logs.fields.causer_type'))->options(['App\Models\User' => __('admin.activity_logs.subject_types.App\Models\User')])->searchable(), Forms\Components\TextInput::make('causer_id')->label(__('admin.activity_logs.fields.causer_id'))->numeric()])->columns(2), Forms\Components\Section::make(__('admin.activity_logs.sections.additional_data'))->schema([Forms\Components\KeyValue::make('properties')->label(__('admin.activity_logs.fields.properties'))->keyLabel(__('admin.activity_logs.fields.property_key'))->valueLabel(__('admin.activity_logs.fields.property_value'))->columnSpanFull(), Forms\Components\TextInput::make('event')->label(__('admin.activity_logs.fields.event'))->maxLength(255), Forms\Components\TextInput::make('batch_uuid')->label(__('admin.activity_logs.fields.batch_uuid'))->maxLength(255)])->columns(2)]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('log_name')->label(__('admin.activity_logs.fields.log_name'))->searchable()->sortable()->badge()->color('primary'), Tables\Columns\TextColumn::make('description')->label(__('admin.activity_logs.fields.description'))->searchable()->sortable()->weight('medium')->limit(50)->tooltip(function (Tables\Columns\TextColumn $column): ?string {
            $state = $column->getState();
            if (strlen($state) <= 50) {
                return null;
            }
            return $state;
        }), Tables\Columns\TextColumn::make('event')->label(__('admin.activity_logs.fields.event'))->searchable()->sortable()->badge()->color(fn(string $state): string => match ($state) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'info',
            default => 'gray',
        }), Tables\Columns\TextColumn::make('subject_type')->label(__('admin.activity_logs.fields.subject_type'))->formatStateUsing(fn($state) => class_basename($state))->searchable()->sortable()->badge()->color('info'), Tables\Columns\TextColumn::make('subject_id')->label(__('admin.activity_logs.fields.subject_id'))->numeric()->sortable(), Tables\Columns\TextColumn::make('causer.name')->label(__('admin.activity_logs.fields.causer'))->searchable()->sortable()->badge()->color('success'), Tables\Columns\TextColumn::make('created_at')->label(__('admin.activity_logs.fields.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('updated_at')->label(__('admin.activity_logs.fields.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([Tables\Filters\SelectFilter::make('log_name')->label(__('admin.activity_logs.fields.log_name'))->options(function () {
            return Activity::distinct('log_name')->pluck('log_name', 'log_name')->toArray();
        }), Tables\Filters\SelectFilter::make('event')->label(__('admin.activity_logs.fields.event'))->options(['created' => __('admin.activity_logs.events.created'), 'updated' => __('admin.activity_logs.events.updated'), 'deleted' => __('admin.activity_logs.events.deleted'), 'restored' => __('admin.activity_logs.events.restored')]), Tables\Filters\SelectFilter::make('subject_type')->label(__('admin.activity_logs.fields.subject_type'))->options(['App\Models\User' => __('admin.activity_logs.subject_types.App\Models\User'), 'App\Models\Product' => __('admin.activity_logs.subject_types.App\Models\Product'), 'App\Models\Order' => __('admin.activity_logs.subject_types.App\Models\Order'), 'App\Models\Category' => __('admin.activity_logs.subject_types.App\Models\Category'), 'App\Models\Brand' => __('admin.activity_logs.subject_types.App\Models\Brand'), 'App\Models\Collection' => __('admin.activity_logs.subject_types.App\Models\Collection'), 'App\Models\Review' => __('admin.activity_logs.subject_types.App\Models\Review'), 'App\Models\Discount' => __('admin.activity_logs.subject_types.App\Models\Discount'), 'App\Models\Coupon' => __('admin.activity_logs.subject_types.App\Models\Coupon'), 'App\Models\Campaign' => __('admin.activity_logs.subject_types.App\Models\Campaign'), 'App\Models\Media' => __('admin.activity_logs.subject_types.App\Models\Media'), 'App\Models\CartItem' => __('admin.activity_logs.subject_types.App\Models\CartItem'), 'App\Models\CustomerGroup' => __('admin.activity_logs.subject_types.App\Models\CustomerGroup'), 'App\Models\LegalPage' => __('admin.activity_logs.subject_types.App\Models\LegalPage'), 'App\Models\Address' => __('admin.activity_logs.subject_types.App\Models\Address'), 'App\Models\Inventory' => __('admin.activity_logs.subject_types.App\Models\Inventory'), 'App\Models\Backup' => __('admin.activity_logs.subject_types.App\Models\Backup'), 'App\Models\Currency' => __('admin.activity_logs.subject_types.App\Models\Currency')]), Tables\Filters\SelectFilter::make('causer')->relationship('causer', 'name')->searchable()->preload(), Tables\Filters\Filter::make('created_at')->form([Forms\Components\DatePicker::make('created_from')->label(__('admin.activity_logs.filters.created_from')), Forms\Components\DatePicker::make('created_until')->label(__('admin.activity_logs.filters.created_until'))])->query(function (Builder $query, array $data): Builder {
            return $query->when($data['created_from'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))->when($data['created_until'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
        }), Tables\Filters\Filter::make('today')->label(__('admin.activity_logs.filters.today'))->query(fn(Builder $query): Builder => $query->whereDate('created_at', today())), Tables\Filters\Filter::make('this_week')->label(__('admin.activity_logs.filters.this_week'))->query(fn(Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])), Tables\Filters\Filter::make('this_month')->label(__('admin.activity_logs.filters.this_month'))->query(fn(Builder $query): Builder => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\Action::make('view_subject')->label(__('admin.activity_logs.actions.view_subject'))->icon('heroicon-o-eye')->color('info')->url(fn(Activity $record): string => match ($record->subject_type) {
            'App\Models\User' => route('filament.admin.resources.users.view', $record->subject_id),
            'App\Models\Product' => route('filament.admin.resources.products.view', $record->subject_id),
            'App\Models\Order' => route('filament.admin.resources.orders.view', $record->subject_id),
            'App\Models\Category' => route('filament.admin.resources.categories.view', $record->subject_id),
            'App\Models\Brand' => route('filament.admin.resources.brands.view', $record->subject_id),
            default => '#',
        })->openUrlInNewTab()->visible(fn(Activity $record): bool => $record->subject_id !== null)])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])->defaultSort('created_at', 'desc')->persistSortInSession()->persistSearchInSession()->persistFiltersInSession();
    }
    /**
     * Handle getRelations functionality with proper error handling.
     * @return array
     */
    public static function getRelations(): array
    {
        return [RelationManagers\SubjectRelationManager::class, RelationManagers\CauserRelationManager::class];
    }
    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return ['index' => Pages\ListActivityLogs::route('/'), 'create' => Pages\CreateActivityLog::route('/create'), 'view' => Pages\ViewActivityLog::route('/{record}'), 'edit' => Pages\EditActivityLog::route('/{record}/edit')];
    }
    /**
     * Handle getWidgets functionality with proper error handling.
     * @return array
     */
    public static function getWidgets(): array
    {
        return [Widgets\ActivityLogStatsWidget::class, Widgets\ActivityLogChartWidget::class, Widgets\RecentActivityLogsWidget::class];
    }
    /**
     * Handle getEloquentQuery functionality with proper error handling.
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
    // Authorization methods
    /**
     * Handle canAccess functionality with proper error handling.
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('administrator') ?? false;
    }
    /**
     * Handle canViewAny functionality with proper error handling.
     * @return bool
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view activity logs') ?? false;
    }
    /**
     * Handle canCreate functionality with proper error handling.
     * @return bool
     */
    public static function canCreate(): bool
    {
        return false;
        // Activity logs are read-only
    }
    /**
     * Handle canEdit functionality with proper error handling.
     * @param mixed $record
     * @return bool
     */
    public static function canEdit($record): bool
    {
        return false;
        // Activity logs are read-only
    }
    /**
     * Handle canDelete functionality with proper error handling.
     * @param mixed $record
     * @return bool
     */
    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete activity logs') ?? false;
    }
}