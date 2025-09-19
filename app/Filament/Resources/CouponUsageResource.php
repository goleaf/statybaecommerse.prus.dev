<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CouponUsageResource\Pages;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
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
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.coupon_usages.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.coupon_usages.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('coupon_usage_tabs')
                ->tabs([
                    Tab::make(__('admin.coupon_usages.form.tabs.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            SchemaSection::make(__('admin.coupon_usages.form.sections.basic_information'))
                                ->schema([
                                    SchemaGrid::make(2)
                                        ->schema([
                                            Select::make('coupon_id')
                                                ->label(__('admin.coupon_usages.form.fields.coupon'))
                                                ->relationship('coupon', 'code')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->code} - {$record->name}")
                                                ->columnSpan(1),
                                            Select::make('user_id')
                                                ->label(__('admin.coupon_usages.form.fields.user'))
                                                ->relationship('user', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->email})")
                                                ->columnSpan(1),
                                        ]),
                                    SchemaGrid::make(2)
                                        ->schema([
                                            Select::make('order_id')
                                                ->label(__('admin.coupon_usages.form.fields.order'))
                                                ->relationship('order', 'id')
                                                ->searchable()
                                                ->preload()
                                                ->getOptionLabelFromRecordUsing(fn($record) => "Order #{$record->id} - {$record->total_amount}€")
                                                ->columnSpan(1),
                                            TextInput::make('discount_amount')
                                                ->label(__('admin.coupon_usages.form.fields.discount_amount'))
                                                ->numeric()
                                                ->prefix('€')
                                                ->required()
                                                ->minValue(0)
                                                ->columnSpan(1),
                                        ]),
                                    DateTimePicker::make('used_at')
                                        ->label(__('admin.coupon_usages.form.fields.used_at'))
                                        ->required()
                                        ->default(now())
                                        ->columnSpanFull(),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.coupon_usages.form.tabs.usage_details'))
                        ->icon('heroicon-o-chart-bar')
                        ->schema([
                            SchemaSection::make(__('admin.coupon_usages.form.sections.usage_details'))
                                ->schema([
                                    Placeholder::make('coupon_name')
                                        ->label(__('admin.coupon_usages.form.fields.coupon_name'))
                                        ->content(fn($record) => $record?->coupon?->name ?? '-'),
                                    Placeholder::make('coupon_discount_type')
                                        ->label(__('admin.coupon_usages.form.fields.coupon_discount_type'))
                                        ->content(fn($record) => $record?->coupon?->discount_type ?? '-'),
                                    Placeholder::make('user_email')
                                        ->label(__('admin.coupon_usages.form.fields.user_email'))
                                        ->content(fn($record) => $record?->user?->email ?? '-'),
                                    Placeholder::make('order_total')
                                        ->label(__('admin.coupon_usages.form.fields.order_total'))
                                        ->content(fn($record) => $record?->order ? '€' . number_format($record->order->total_amount, 2) : '-'),
                                ])
                                ->columns(2),
                        ]),
                    Tab::make(__('admin.coupon_usages.form.tabs.metadata'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            SchemaSection::make(__('admin.coupon_usages.form.sections.metadata'))
                                ->schema([
                                    KeyValue::make('metadata')
                                        ->label(__('admin.coupon_usages.form.fields.metadata'))
                                        ->keyLabel(__('admin.coupon_usages.form.fields.key'))
                                        ->valueLabel(__('admin.coupon_usages.form.fields.value'))
                                        ->columnSpanFull(),
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
                TextColumn::make('coupon.code')
                    ->label(__('admin.coupon_usages.form.fields.coupon'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label(__('admin.coupon_usages.form.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.id')
                    ->label(__('admin.coupon_usages.form.fields.order'))
                    ->formatStateUsing(fn($state) => $state ? "Order #{$state}" : '-')
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->label(__('admin.coupon_usages.form.fields.discount_amount'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('used_at')
                    ->label(__('admin.coupon_usages.form.fields.used_at'))
                    ->dateTime()
                    ->sortable(),
                BadgeColumn::make('usage_period')
                    ->label(__('admin.coupon_usages.form.fields.usage_period'))
                    ->formatStateUsing(function ($record) {
                        if (!$record->used_at)
                            return '-';

                        $usedAt = $record->used_at;
                        if ($usedAt->isToday())
                            return __('admin.coupon_usages.periods.today');
                        if ($usedAt->isThisWeek())
                            return __('admin.coupon_usages.periods.this_week');
                        if ($usedAt->isThisMonth())
                            return __('admin.coupon_usages.periods.this_month');
                        return __('admin.coupon_usages.periods.older');
                    })
                    ->colors([
                        'success' => fn($state) => in_array($state, [__('admin.coupon_usages.periods.today'), __('admin.coupon_usages.periods.this_week')]),
                        'warning' => fn($state) => $state === __('admin.coupon_usages.periods.this_month'),
                        'danger' => fn($state) => $state === __('admin.coupon_usages.periods.older'),
                    ]),
            ])
            ->filters([
                SelectFilter::make('coupon_id')
                    ->label(__('admin.coupon_usages.filters.coupon'))
                    ->relationship('coupon', 'code')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label(__('admin.coupon_usages.filters.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('order_id')
                    ->label(__('admin.coupon_usages.filters.order'))
                    ->relationship('order', 'id')
                    ->searchable()
                    ->preload(),
                DateFilter::make('used_at')
                    ->label(__('admin.coupon_usages.filters.used_at')),
                Filter::make('used_today')
                    ->label(__('admin.coupon_usages.filters.used_today'))
                    ->query(fn(Builder $query): Builder => $query->usedToday()),
                Filter::make('used_this_week')
                    ->label(__('admin.coupon_usages.filters.used_this_week'))
                    ->query(fn(Builder $query): Builder => $query->usedThisWeek()),
                Filter::make('used_this_month')
                    ->label(__('admin.coupon_usages.filters.used_this_month'))
                    ->query(fn(Builder $query): Builder => $query->usedThisMonth()),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                TableBulkAction::make('export_usage_report')
                    ->label(__('admin.coupon_usages.actions.export_usage_report'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function (CouponUsage $record): void {
                        // Export usage report logic here
                        FilamentNotification::make()
                            ->title(__('admin.coupon_usages.usage_report_exported_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('export_bulk_report')
                        ->label(__('admin.coupon_usages.actions.export_bulk_report'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function (EloquentCollection $records): void {
                            // Export bulk usage report logic here
                            FilamentNotification::make()
                                ->title(__('admin.coupon_usages.bulk_report_exported_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('used_at', 'desc');
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
            'index' => Pages\ListCouponUsages::route('/'),
            'create' => Pages\CreateCouponUsage::route('/create'),
            'view' => Pages\ViewCouponUsage::route('/{record}'),
            'edit' => Pages\EditCouponUsage::route('/{record}/edit'),
        ];
    }
}

