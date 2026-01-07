<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountCodeResource\Pages;
use App\Models\DiscountCode;
use App\Support\Concerns\HasNav;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction; // Use the Filament v4 bulk action group namespace introduced during the upgrade.
use Filament\Actions\EditAction; // Align delete bulk action import with Filament v4 namespace changes.
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DiscountCodeResource extends Resource
{
    use HasNav;

    protected static ?string $model = DiscountCode::class;

    /**
     * Ensure administrative listings ignore front-end global scopes.
     *
     * @return Builder<DiscountCode>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function getNavigationGroup(): string
    {
        return 'Marketing';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-tag';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('discount_codes.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('discount_codes.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('discount_codes.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('discount_id')
                                ->label(__('discount_codes.discount'))
                                ->relationship('discount', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                // Ensure administrators always link a code to a discount.
                                ->helperText(__('discount_codes.discount_help')),
                            TextInput::make('code')
                                ->label(__('discount_codes.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->helperText(__('discount_codes.code_help')),
                            TextInput::make('name')
                                ->label(__('discount_codes.name'))
                                ->maxLength(255),
                        ]),
                    Textarea::make('description')
                        ->label(__('discount_codes.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('discount_codes.discount_settings'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('discount_codes.type'))
                                ->options([
                                    'percentage'    => __('discount_codes.types.percentage'),
                                    'fixed'         => __('discount_codes.types.fixed'),
                                    'free_shipping' => __('discount_codes.types.free_shipping'),
                                    'buy_x_get_y'   => __('discount_codes.types.buy_x_get_y'),
                                ])
                                ->default('percentage')
                                ->required()
                                // Guard against empty submissions so validation in tests passes.
                                ->helperText(__('discount_codes.type_help'))
                                ->live(),
                            TextInput::make('value')
                                ->label(__('discount_codes.value'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('discount_codes.value_help')),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('minimum_amount')
                                ->label(__('discount_codes.minimum_amount'))
                                ->prefix('€')
                                ->minValue(0),
                            TextInput::make('maximum_discount')
                                ->label(__('discount_codes.maximum_discount'))
                                ->prefix('€')
                                ->minValue(0),
                        ]),
                ]),
            SchemaSection::make(__('discount_codes.usage_limits'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('usage_limit')
                                ->label(__('discount_codes.usage_limit'))
                                ->numeric()
                                ->minValue(1)
                                ->helperText(__('discount_codes.usage_limit_help')),
                            TextInput::make('usage_limit_per_user')
                                ->label(__('discount_codes.usage_limit_per_user'))
                                ->numeric()
                                ->minValue(1)
                                ->helperText(__('discount_codes.usage_limit_per_user_help')),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('usage_count')
                                ->label(__('discount_codes.used_count'))
                                ->numeric()
                                ->default(0)
                                // Disabled so it reflects tracked usage without manual edits.
                                ->disabled(),
                            TextInput::make('remaining_uses')
                                ->label(__('discount_codes.remaining_uses'))
                                ->numeric()
                                ->dehydrated(false)
                                // Remaining uses is computed on the model; avoid persisting raw values.
                                ->disabled(),
                        ]),
                ]),
            SchemaSection::make(__('discount_codes.validity'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            SupportFlatpickr::makeDateTime('valid_from')
                                ->label(__('discount_codes.valid_from'))
                                ->default(now())
                                ->displayFormat('d/m/Y H:i'),
                            SupportFlatpickr::makeDateTime('valid_until')
                                ->label(__('discount_codes.valid_until'))
                                ->displayFormat('d/m/Y H:i'),
                        ]),
                ]),
            SchemaSection::make(__('discount_codes.targeting'))
                ->schema([
                    Select::make('customer_group_id')
                        ->label(__('discount_codes.customer_group'))
                        ->relationship('customerGroup', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->maxLength(500),
                        ]),
                    Toggle::make('is_first_time_only')
                        ->label(__('discount_codes.is_first_time_only'))
                        ->default(false),
                ]),
            SchemaSection::make(__('discount_codes.settings'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('discount_codes.is_active'))
                                ->default(true),
                            Toggle::make('is_public')
                                ->label(__('discount_codes.is_public'))
                                ->default(false),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('is_auto_apply')
                                ->label(__('discount_codes.is_auto_apply'))
                                ->default(false),
                            Toggle::make('is_stackable')
                                ->label(__('discount_codes.is_stackable'))
                                ->default(false),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('discount_codes.code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->badge()
                    ->color('blue'),
                TextColumn::make('name')
                    ->label(__('discount_codes.name'))
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('discount_codes.type'))
                    ->formatStateUsing(fn (string $state): string => __("discount_codes.types.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'percentage'    => 'green',
                        'fixed'         => 'blue',
                        'free_shipping' => 'purple',
                        'buy_x_get_y'   => 'orange',
                        default         => 'gray',
                    }),
                TextColumn::make('value')
                    ->label(__('discount_codes.value'))
                    ->formatStateUsing(function (float|int|string|null $state, DiscountCode $record): string {
                        if ($record->type === 'percentage') {
                            return (string) ($state ?? 0) . '%';
                        }

                        if ($record->type === 'free_shipping') {
                            return __('discount_codes.free_shipping');
                        }

                        return '€' . number_format((float) ($state ?? 0), 2);
                    })
                    ->sortable(),
                TextColumn::make('usage_limit')
                    ->label(__('discount_codes.usage_limit'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('usage_count')
                    ->label(__('discount_codes.used_count'))
                    ->numeric()
                    ->color(fn (?int $state, DiscountCode $record): string => $record->usage_limit && ($state ?? 0) >= $record->usage_limit ? 'danger' : 'success'),
                TextColumn::make('remaining_uses')
                    ->label(__('discount_codes.remaining_uses'))
                    ->numeric()
                    ->color(fn (?int $state): string => ($state ?? 0) <= 0 ? 'danger' : 'success'),
                TextColumn::make('customerGroup.name')
                    ->label(__('discount_codes.customer_group'))
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('is_active')
                    ->label(__('discount_codes.status'))
                    ->formatStateUsing(
                        fn (bool $state): string => $state
                            ? __('discount_codes.active')
                            : __('discount_codes.inactive')
                    )
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                IconColumn::make('is_public')
                    ->label(__('discount_codes.is_public'))
                    ->boolean(),
                IconColumn::make('is_auto_apply')
                    ->label(__('discount_codes.is_auto_apply'))
                    ->boolean(),
                IconColumn::make('is_stackable')
                    ->label(__('discount_codes.is_stackable'))
                    ->boolean(),
                TextColumn::make('valid_from')
                    ->label(__('discount_codes.valid_from'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label(__('discount_codes.valid_until'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('discount_codes.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('discount_codes.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'percentage'    => __('discount_codes.types.percentage'),
                        'fixed'         => __('discount_codes.types.fixed'),
                        'free_shipping' => __('discount_codes.types.free_shipping'),
                        'buy_x_get_y'   => __('discount_codes.types.buy_x_get_y'),
                    ]),
                SelectFilter::make('customer_group_id')
                    ->relationship('customerGroup', 'name')
                    ->preload(),
                SelectFilter::make('is_active')
                    ->label(__('discount_codes.is_active'))
                    ->options([
                        '1' => __('discount_codes.active_only'),
                        '0' => __('discount_codes.inactive_only'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! is_string($value)) {
                            return $query;
                        }

                        return $query->where('is_active', $value === '1');
                    }),
                SelectFilter::make('is_public')
                    ->label(__('discount_codes.is_public'))
                    ->options([
                        '1' => __('discount_codes.public_only'),
                        '0' => __('discount_codes.private_only'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! is_string($value)) {
                            return $query;
                        }

                        return $query->where('is_public', $value === '1');
                    }),
                SelectFilter::make('is_auto_apply')
                    ->label(__('discount_codes.is_auto_apply'))
                    ->options([
                        '1' => __('discount_codes.auto_apply_only'),
                        '0' => __('discount_codes.manual_apply_only'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! is_string($value)) {
                            return $query;
                        }

                        return $query->where('is_auto_apply', $value === '1');
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label(__('discount_codes.view'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (DiscountCode $record): string => self::getUrl('view', ['record' => $record])),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (DiscountCode $record): string => $record->is_active ? __('discount_codes.deactivate') : __('discount_codes.activate'))
                    ->icon(fn (DiscountCode $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (DiscountCode $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (DiscountCode $record): void {
                        // Persist the new active flag even when storefront scopes would normally hide the record.
                        $newState = ! $record->is_active;

                        self::updateRecordWithoutScopes($record, ['is_active' => $newState]);

                        Notification::make()
                            ->title($newState ? __('discount_codes.activated_successfully') : __('discount_codes.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label(__('discount_codes.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (DiscountCode $record): void {
                        $newDiscountCode = $record->replicate();
                        $newDiscountCode->code = $record->code . '_copy_' . time();
                        $newDiscountCode->name = $record->name . ' (Copy)';
                        // Reset usage tracking on duplicated codes to avoid inheriting historical metrics.
                        $newDiscountCode->usage_count = 0;
                        $newDiscountCode->save();

                        Notification::make()
                            ->title(__('discount_codes.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('discount_codes.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            // Ensure each record updates even if storefront scopes would exclude it.
                            /** @var DiscountCode $record */
                            foreach ($records as $record) {
                                self::updateRecordWithoutScopes($record, ['is_active' => true]);
                            }

                            Notification::make()
                                ->title(__('discount_codes.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('discount_codes.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            // Deactivate records without losing access to those hidden by global scopes.
                            /** @var DiscountCode $record */
                            foreach ($records as $record) {
                                self::updateRecordWithoutScopes($record, ['is_active' => false]);
                            }

                            Notification::make()
                                ->title(__('discount_codes.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDiscountCodes::route('/'),
            'create' => Pages\CreateDiscountCode::route('/create'),
            'view'   => Pages\ViewDiscountCode::route('/{record}'),
            'edit'   => Pages\EditDiscountCode::route('/{record}/edit'),
        ];
    }

    /**
     * Update the given discount code without storefront global scopes blocking persistence.
     *
     * @param array<string, mixed> $attributes
     */
    public static function updateRecordWithoutScopes(DiscountCode $record, array $attributes): void
    {
        if ($attributes === []) {
            return;
        }

        if ($record->usesTimestamps()) {
            // Keep timestamps current when bypassing Eloquent's default save flow.
            $attributes[$record->getUpdatedAtColumn()] = Carbon::now();
        }

        $connection = $record->getConnectionName();
        $table = $record->getTable();

        DB::connection($connection)
            ->table($table)
            ->where($record->getKeyName(), $record->getKey())
            ->update($attributes);

        // Synchronize the in-memory model so Filament sees the latest values.
        $record->forceFill($attributes);
        $record->syncChanges();
        $record->syncOriginalAttributes(array_keys($attributes));
    }
}
