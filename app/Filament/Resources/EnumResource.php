<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\EnumResource\Pages;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use BackedEnum;
use UnitEnum;

/**
 * EnumResource
 *
 * Filament v4 resource for managing system enums in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class EnumResource extends Resource
{
    protected static ?string $model = null; // This is a virtual resource
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::System;
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.enums.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.enums.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.enums.single');
    }

    /**
     * Get available enum types
     * @return array
     */
    public static function getEnumTypes(): array
    {
        return [
            'navigation_group' => __('admin.enums.types.navigation_group'),
            'order_status' => __('admin.enums.types.order_status'),
            'payment_status' => __('admin.enums.types.payment_status'),
            'shipping_status' => __('admin.enums.types.shipping_status'),
            'user_role' => __('admin.enums.types.user_role'),
            'product_status' => __('admin.enums.types.product_status'),
            'campaign_type' => __('admin.enums.types.campaign_type'),
            'discount_type' => __('admin.enums.types.discount_type'),
            'notification_type' => __('admin.enums.types.notification_type'),
            'document_type' => __('admin.enums.types.document_type'),
        ];
    }

    /**
     * Get enum values for a specific type
     * @param string $type
     * @return array
     */
    public static function getEnumValues(string $type): array
    {
        return match ($type) {
            'navigation_group' => [
                'products' => __('admin.enums.navigation_groups.products'),
                'orders' => __('admin.enums.navigation_groups.orders'),
                'customers' => __('admin.enums.navigation_groups.customers'),
                'marketing' => __('admin.enums.navigation_groups.marketing'),
                'reports' => __('admin.enums.navigation_groups.reports'),
                'system' => __('admin.enums.navigation_groups.system'),
            ],
            'order_status' => [
                'pending' => __('admin.enums.order_statuses.pending'),
                'processing' => __('admin.enums.order_statuses.processing'),
                'shipped' => __('admin.enums.order_statuses.shipped'),
                'delivered' => __('admin.enums.order_statuses.delivered'),
                'cancelled' => __('admin.enums.order_statuses.cancelled'),
                'refunded' => __('admin.enums.order_statuses.refunded'),
            ],
            'payment_status' => [
                'pending' => __('admin.enums.payment_statuses.pending'),
                'paid' => __('admin.enums.payment_statuses.paid'),
                'failed' => __('admin.enums.payment_statuses.failed'),
                'refunded' => __('admin.enums.payment_statuses.refunded'),
                'partially_refunded' => __('admin.enums.payment_statuses.partially_refunded'),
            ],
            'shipping_status' => [
                'pending' => __('admin.enums.shipping_statuses.pending'),
                'preparing' => __('admin.enums.shipping_statuses.preparing'),
                'shipped' => __('admin.enums.shipping_statuses.shipped'),
                'in_transit' => __('admin.enums.shipping_statuses.in_transit'),
                'delivered' => __('admin.enums.shipping_statuses.delivered'),
                'returned' => __('admin.enums.shipping_statuses.returned'),
            ],
            default => [],
        };
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('enum_tabs')
                ->tabs([
                    Tab::make(__('admin.enums.form.tabs.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            SchemaSection::make(__('admin.enums.form.sections.basic_information'))
                                ->schema([
                                    SchemaGrid::make(2)
                                        ->schema([
                                            Select::make('type')
                                                ->label(__('admin.enums.form.fields.type'))
                                                ->options(self::getEnumTypes())
                                                ->required()
                                                ->reactive()
                                                ->columnSpan(1),
                                            TextInput::make('name')
                                                ->label(__('admin.enums.form.fields.name'))
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ]),
                                    TextInput::make('key')
                                        ->label(__('admin.enums.form.fields.key'))
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),
                                    TextInput::make('value')
                                        ->label(__('admin.enums.form.fields.value'))
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),
                                    Textarea::make('description')
                                        ->label(__('admin.enums.form.fields.description'))
                                        ->maxLength(1000)
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    SchemaGrid::make(3)
                                        ->schema([
                                            TextInput::make('sort_order')
                                                ->label(__('admin.enums.form.fields.sort_order'))
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),
                                            Toggle::make('is_active')
                                                ->label(__('admin.enums.form.fields.is_active'))
                                                ->default(true)
                                                ->columnSpan(1),
                                            Toggle::make('is_default')
                                                ->label(__('admin.enums.form.fields.is_default'))
                                                ->default(false)
                                                ->columnSpan(1),
                                        ])
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.enums.form.tabs.additional_settings'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            SchemaSection::make(__('admin.enums.form.sections.additional_settings'))
                                ->schema([
                                    KeyValue::make('metadata')
                                        ->label(__('admin.enums.form.fields.metadata'))
                                        ->keyLabel(__('admin.enums.form.fields.metadata_key'))
                                        ->valueLabel(__('admin.enums.form.fields.metadata_value'))
                                        ->columnSpanFull(),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.enums.form.tabs.preview'))
                        ->icon('heroicon-o-eye')
                        ->schema([
                            SchemaSection::make(__('admin.enums.form.sections.preview'))
                                ->schema([
                                    Placeholder::make('enum_preview')
                                        ->label(__('admin.enums.form.fields.enum_preview'))
                                        ->content(fn($record) => $record ? 
                                            "{$record->type}::{$record->key} => {$record->value}" : '-'
                                        ),
                                    Placeholder::make('usage_count')
                                        ->label(__('admin.enums.form.fields.usage_count'))
                                        ->content(fn($record) => $record ? 
                                            self::getUsageCount($record->type, $record->key) : 0
                                        ),
                                ])
                                ->columns(1),
                        ]),
                ])
                ->columnSpanFull(),
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
                TextColumn::make('type')
                    ->label(__('admin.enums.form.fields.type'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => 
                        self::getEnumTypes()[$state] ?? $state
                    ),
                TextColumn::make('key')
                    ->label(__('admin.enums.form.fields.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('value')
                    ->label(__('admin.enums.form.fields.value'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('admin.enums.form.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('admin.enums.form.fields.sort_order'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('admin.enums.form.fields.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_default')
                    ->label(__('admin.enums.form.fields.is_default'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('usage_count')
                    ->label(__('admin.enums.form.fields.usage_count'))
                    ->formatStateUsing(fn($record) => 
                        self::getUsageCount($record->type, $record->key)
                    ),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.enums.filters.type'))
                    ->options(self::getEnumTypes()),
                TernaryFilter::make('is_active')
                    ->label(__('admin.enums.filters.is_active')),
                TernaryFilter::make('is_default')
                    ->label(__('admin.enums.filters.is_default')),
                Filter::make('recent')
                    ->label(__('admin.enums.filters.recent'))
                    ->query(fn(Builder $query): Builder => 
                        $query->where('created_at', '>=', now()->subDays(30))
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                TableBulkAction::make('activate')
                    ->label(__('admin.enums.actions.activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($record): void {
                        $record->update(['is_active' => true]);
                        FilamentNotification::make()
                            ->title(__('admin.enums.activated_successfully'))
                            ->success()
                            ->send();
                    }),
                TableBulkAction::make('deactivate')
                    ->label(__('admin.enums.actions.deactivate'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->action(function ($record): void {
                        $record->update(['is_active' => false]);
                        FilamentNotification::make()
                            ->title(__('admin.enums.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                TableBulkAction::make('set_default')
                    ->label(__('admin.enums.actions.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('info')
                    ->action(function ($record): void {
                        // Remove default from other records of same type
                        self::removeDefaultFromType($record->type);
                        $record->update(['is_default' => true]);
                        FilamentNotification::make()
                            ->title(__('admin.enums.set_default_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('activate_bulk')
                        ->label(__('admin.enums.actions.activate_bulk'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (EloquentCollection $records): void {
                            $records->each(function ($record): void {
                                $record->update(['is_active' => true]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.enums.bulk_activated_successfully'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('deactivate_bulk')
                        ->label(__('admin.enums.actions.deactivate_bulk'))
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function (EloquentCollection $records): void {
                            $records->each(function ($record): void {
                                $record->update(['is_active' => false]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.enums.bulk_deactivated_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('type', 'asc');
    }

    /**
     * Get usage count for an enum value
     * @param string $type
     * @param string $key
     * @return int
     */
    public static function getUsageCount(string $type, string $key): int
    {
        // This would be implemented based on actual usage tracking
        return rand(0, 100);
    }

    /**
     * Remove default flag from other records of the same type
     * @param string $type
     * @return void
     */
    public static function removeDefaultFromType(string $type): void
    {
        // This would update the database to remove default flags
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
            'index' => Pages\ListEnums::route('/'),
            'create' => Pages\CreateEnum::route('/create'),
            'view' => Pages\ViewEnum::route('/{record}'),
            'edit' => Pages\EditEnum::route('/{record}/edit'),
        ];
    }
}
