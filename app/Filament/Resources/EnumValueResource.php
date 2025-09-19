<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\EnumValueResource\Pages;
use App\Models\EnumValue;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use BackedEnum;

/**
 * EnumValueResource
 *
 * Filament v4 resource for EnumValue management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class EnumValueResource extends Resource
{
    protected static ?string $model = EnumValue::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    // /** @var UnitEnum|string|null */\n    // protected static $navigationGroup = NavigationGroup::System;
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.enum_values.title');
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
        return __('admin.enum_values.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.enum_values.single');
    }

    /**
     * Get available enum types
     * @return array
     */
    public static function getEnumTypes(): array
    {
        return [
            'navigation_group' => __('admin.enum_values.types.navigation_group'),
            'order_status' => __('admin.enum_values.types.order_status'),
            'payment_status' => __('admin.enum_values.types.payment_status'),
            'shipping_status' => __('admin.enum_values.types.shipping_status'),
            'user_role' => __('admin.enum_values.types.user_role'),
            'product_status' => __('admin.enum_values.types.product_status'),
            'campaign_type' => __('admin.enum_values.types.campaign_type'),
            'discount_type' => __('admin.enum_values.types.discount_type'),
            'notification_type' => __('admin.enum_values.types.notification_type'),
            'document_type' => __('admin.enum_values.types.document_type'),
            'address_type' => __('admin.enum_values.types.address_type'),
            'priority' => __('admin.enum_values.types.priority'),
            'status' => __('admin.enum_values.types.status'),
        ];
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('enum_value_tabs')
                ->tabs([
                    Tab::make(__('admin.enum_values.form.tabs.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            SchemaSection::make(__('admin.enum_values.form.sections.basic_information'))
                                ->schema([
                                    SchemaGrid::make(2)
                                        ->schema([
                                            Select::make('type')
                                                ->label(__('admin.enum_values.form.fields.type'))
                                                ->options(self::getEnumTypes())
                                                ->required()
                                                ->reactive()
                                                ->columnSpan(1),
                                            TextInput::make('name')
                                                ->label(__('admin.enum_values.form.fields.name'))
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ]),
                                    SchemaGrid::make(2)
                                        ->schema([
                                            TextInput::make('key')
                                                ->label(__('admin.enum_values.form.fields.key'))
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                            TextInput::make('value')
                                                ->label(__('admin.enum_values.form.fields.value'))
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ]),
                                    Textarea::make('description')
                                        ->label(__('admin.enum_values.form.fields.description'))
                                        ->maxLength(1000)
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    SchemaGrid::make(3)
                                        ->schema([
                                            TextInput::make('sort_order')
                                                ->label(__('admin.enum_values.form.fields.sort_order'))
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),
                                            Toggle::make('is_active')
                                                ->label(__('admin.enum_values.form.fields.is_active'))
                                                ->default(true)
                                                ->columnSpan(1),
                                            Toggle::make('is_default')
                                                ->label(__('admin.enum_values.form.fields.is_default'))
                                                ->default(false)
                                                ->columnSpan(1),
                                        ])
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.enum_values.form.tabs.additional_settings'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            SchemaSection::make(__('admin.enum_values.form.sections.additional_settings'))
                                ->schema([
                                    KeyValue::make('metadata')
                                        ->label(__('admin.enum_values.form.fields.metadata'))
                                        ->keyLabel(__('admin.enum_values.form.fields.metadata_key'))
                                        ->valueLabel(__('admin.enum_values.form.fields.metadata_value'))
                                        ->columnSpanFull(),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.enum_values.form.tabs.preview'))
                        ->icon('heroicon-o-eye')
                        ->schema([
                            SchemaSection::make(__('admin.enum_values.form.sections.preview'))
                                ->schema([
                                    Placeholder::make('enum_preview')
                                        ->label(__('admin.enum_values.form.fields.enum_preview'))
                                        ->content(fn($record) => $record
                                            ? "{$record->type}::{$record->key} => {$record->value}"
                                            : '-'),
                                    Placeholder::make('usage_count')
                                        ->label(__('admin.enum_values.form.fields.usage_count'))
                                        ->content(fn($record) => $record?->usage_count ?? 0),
                                    Placeholder::make('formatted_value')
                                        ->label(__('admin.enum_values.form.fields.formatted_value'))
                                        ->content(fn($record) => $record?->formatted_value ?? '-'),
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
                    ->label(__('admin.enum_values.form.fields.type'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string =>
                        self::getEnumTypes()[$state] ?? $state),
                TextColumn::make('key')
                    ->label(__('admin.enum_values.form.fields.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('value')
                    ->label(__('admin.enum_values.form.fields.value'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('admin.enum_values.form.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('admin.enum_values.form.fields.sort_order'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('admin.enum_values.form.fields.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_default')
                    ->label(__('admin.enum_values.form.fields.is_default'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('usage_count')
                    ->label(__('admin.enum_values.form.fields.usage_count'))
                    ->formatStateUsing(fn($record) => $record->usage_count),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.enum_values.filters.type'))
                    ->options(self::getEnumTypes()),
                TernaryFilter::make('is_active')
                    ->label(__('admin.enum_values.filters.is_active')),
                TernaryFilter::make('is_default')
                    ->label(__('admin.enum_values.filters.is_default')),
                Filter::make('recent')
                    ->label(__('admin.enum_values.filters.recent'))
                    ->query(fn(Builder $query): Builder =>
                        $query->where('created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                TableBulkAction::make('activate')
                    ->label(__('admin.enum_values.actions.activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (EnumValue $record): void {
                        $record->update(['is_active' => true]);
                        FilamentNotification::make()
                            ->title(__('admin.enum_values.activated_successfully'))
                            ->success()
                            ->send();
                    }),
                TableBulkAction::make('deactivate')
                    ->label(__('admin.enum_values.actions.deactivate'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->action(function (EnumValue $record): void {
                        $record->update(['is_active' => false]);
                        FilamentNotification::make()
                            ->title(__('admin.enum_values.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                TableBulkAction::make('set_default')
                    ->label(__('admin.enum_values.actions.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('info')
                    ->action(function (EnumValue $record): void {
                        // Remove default from other records of same type
                        EnumValue::where('type', $record->type)
                            ->where('id', '!=', $record->id)
                            ->update(['is_default' => false]);
                        $record->update(['is_default' => true]);
                        FilamentNotification::make()
                            ->title(__('admin.enum_values.set_default_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('activate_bulk')
                        ->label(__('admin.enum_values.actions.activate_bulk'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (EloquentCollection $records): void {
                            $records->each(function (EnumValue $record): void {
                                $record->update(['is_active' => true]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.enum_values.bulk_activated_successfully'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('deactivate_bulk')
                        ->label(__('admin.enum_values.actions.deactivate_bulk'))
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function (EloquentCollection $records): void {
                            $records->each(function (EnumValue $record): void {
                                $record->update(['is_active' => false]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.enum_values.bulk_deactivated_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('type', 'asc');
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
            'index' => Pages\ListEnumValues::route('/'),
            'create' => Pages\CreateEnumValue::route('/create'),
            'view' => Pages\ViewEnumValue::route('/{record}'),
            'edit' => Pages\EditEnumValue::route('/{record}/edit'),
        ];
    }
}
