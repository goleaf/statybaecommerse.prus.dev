<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Size;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Tapp\FilamentValueRangeFilter\Filters\ValueRangeFilter;
use App\Support\Filament\Forms\Components\Flatpickr;

final class CouponResource extends Resource
{
    use HasNav;

    protected static ?string $model = Coupon::class;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('coupons.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('coupons.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('coupons.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('code')
                                ->label(__('coupons.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash'])
                                ->helperText(__('coupons.code_help')),
                            TextInput::make('name')
                                ->label(__('coupons.name'))
                                ->maxLength(255),
                        ]),
                    Textarea::make('description')
                        ->label(__('coupons.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('coupons.discount_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('coupons.type'))
                                ->options([
                                    'percentage'    => __('coupons.types.percentage'),
                                    'fixed'         => __('coupons.types.fixed'),
                                    'free_shipping' => __('coupons.types.free_shipping'),
                                ])
                                ->default('percentage')
                                ->live(),
                            TextInput::make('value')
                                ->label(__('coupons.value'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->required(fn (Get $get): bool => $get('type') !== 'free_shipping')
                                ->helperText(__('coupons.value_help')),
                            TextInput::make('minimum_amount')
                                ->label(__('coupons.minimum_amount'))
                                ->numeric()
                                ->nullable()
                                ->prefix('€')
                                ->numeric()
                                ->minValue(0)
                                ->nullable(),
                            TextInput::make('maximum_discount')
                                ->label(__('coupons.maximum_discount'))
                                ->numeric()
                                ->nullable()
                                ->prefix('€')
                                ->numeric()
                                ->minValue(0)
                                ->nullable(),
                        ]),
                ]),
            Section::make(__('coupons.usage_limits'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Quantity::make('usage_limit')
                                ->label(__('coupons.usage_limit'))
                                ->minValue(1)
                                ->steps(1)
                                ->nullable()
                                ->helperText(__('coupons.usage_limit_help')),
                            Quantity::make('usage_limit_per_user')
                                ->label(__('coupons.usage_limit_per_user'))
                                ->minValue(1)
                                ->steps(1)
                                ->nullable()
                                ->helperText(__('coupons.usage_limit_per_user_help')),
                            Quantity::make('used_count')
                                ->label(__('coupons.used_count'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0)
                                ->disabled(),
                            Quantity::make('remaining_uses')
                                ->label(__('coupons.remaining_uses'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0)
                                ->disabled(),
                        ]),
                ]),
            Section::make(__('coupons.validity'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Flatpickr::make('valid_from')->asDateTime()
                                ->label(__('coupons.valid_from'))
                                ->default(now())
                                ->displayFormat('d/m/Y H:i'),
                            Flatpickr::make('valid_until')->asDateTime()
                                ->label(__('coupons.valid_until'))
                                ->displayFormat('d/m/Y H:i'),
                        ]),
                ]),
            Section::make(__('coupons.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('coupons.is_active'))
                                ->default(true),
                            Toggle::make('is_public')
                                ->label(__('coupons.is_public'))
                                ->default(false),
                            Toggle::make('is_auto_apply')
                                ->label(__('coupons.is_auto_apply'))
                                ->default(false),
                            Toggle::make('is_stackable')
                                ->label(__('coupons.is_stackable'))
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
        return $table
            ->columns([
                BadgeableColumn::make('code')
                    ->label(__('coupons.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->prefixBadges([
                        Badge::make('status')
                            ->label(fn (Coupon $record): string => $record->is_active ? __('discount_codes.active') : __('discount_codes.inactive'))
                            ->color(fn (Coupon $record): string => $record->is_active ? 'success' : 'danger'),
                    ])
                    ->suffixBadges(function (Coupon $record): array {
                        $badges = [];

                        if ($record->type) {
                            $badges[] = Badge::make('type-' . $record->type)
                                ->label(__('discount_codes.types.' . $record->type))
                                ->color('primary');
                        }

                        if ($record->is_public) {
                            $badges[] = Badge::make('visibility-public')
                                ->label(__('discount_codes.is_public'))
                                ->color('success');
                        } else {
                            $badges[] = Badge::make('visibility-private')
                                ->label(__('discount_codes.private_only'))
                                ->color('gray');
                        }

                        if ($record->is_auto_apply) {
                            $badges[] = Badge::make('auto-apply')
                                ->label(__('discount_codes.is_auto_apply'))
                                ->color('info');
                        }

                        if ($record->is_stackable) {
                            $badges[] = Badge::make('stackable')
                                ->label(__('discount_codes.is_stackable'))
                                ->color('warning');
                        }

                        if ($record->valid_from) {
                            $badges[] = Badge::make('valid-from')
                                ->label(__('discount_codes.valid_from') . ': ' . $record->valid_from->format('Y-m-d'))
                                ->color('secondary');
                        }

                        if ($record->valid_until) {
                            $badges[] = Badge::make('valid-until')
                                ->label(__('discount_codes.valid_until') . ': ' . $record->valid_until->format('Y-m-d'))
                                ->color('secondary');
                        }

                        return $badges;
                    })
                    ->asPills()
                    ->separator('•')
                    ->size(Size::Small),
                TextColumn::make('name')
                    ->label(__('coupons.name'))
                    ->limit(50),
                TextColumn::make('type')
                    ->label(__('coupons.type'))
                    ->formatStateUsing(fn (?string $state): string => $state ? __("coupons.types.{$state}") : '—')
                    ->color(fn (?string $state): string => match ($state) {
                        'percentage'    => 'green',
                        'fixed'         => 'blue',
                        'free_shipping' => 'purple',
                        default         => 'gray',
                    }),
                TextColumn::make('value')
                    ->label(__('coupons.value'))
                    ->formatStateUsing(function ($state, Coupon $record): string {
                        if ($record->type === 'percentage') {
                            return is_null($state) ? '—' : $state . '%';
                        }

                        if ($record->type === 'free_shipping') {
                            return __('coupons.free_shipping');
                        }

                        if (is_null($state)) {
                            return '—';
                        }

                        return '€' . number_format((float) $state, 2);
                    })
                    ->sortable(),
                TextColumn::make('usage_limit')
                    ->label(__('coupons.usage_limit'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('used_count')
                    ->label(__('coupons.used_count'))
                    ->color(fn ($state, Coupon $record): string => $record->usage_limit && $state >= $record->usage_limit ? 'danger' : 'success'),
                TextColumn::make('remaining_uses')
                    ->label(__('coupons.remaining_uses'))
                    ->color(fn ($state): string => $state <= 0 ? 'danger' : 'success'),
                TextColumn::make('customerGroup.name')
                    ->label(__('coupons.customer_group'))
                    ->color('gray'),
                IconColumn::make('is_public')
                    ->label(__('coupons.is_public'))
                    ->boolean(),
                IconColumn::make('is_auto_apply')
                    ->label(__('coupons.is_auto_apply'))
                    ->boolean(),
                IconColumn::make('is_stackable')
                    ->label(__('coupons.is_stackable'))
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->label(__('coupons.starts_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label(__('coupons.expires_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('coupons.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('coupons.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'percentage'    => __('coupons.types.percentage'),
                        'fixed'         => __('coupons.types.fixed'),
                        'free_shipping' => __('coupons.types.free_shipping'),
                    ]),
                SelectFilter::make('customer_group_id')
                    ->relationship('customerGroup', 'name')
                    ->preload(),
                ValueRangeFilter::make('minimum_amount')
                    ->label(__('coupons.minimum_amount'))
                    ->currency()
                    ->currencyCode('EUR')
                    ->locale('lt')
                    ->currencyInSmallestUnit(false),
                ValueRangeFilter::make('value')
                    ->label(__('coupons.value')),
                ValueRangeFilter::make('usage_limit')
                    ->label(__('coupons.usage_limit')),
                ValueRangeFilter::make('used_count')
                    ->label(__('coupons.used_count')),
                TernaryFilter::make('is_active')
                    ->label(__('coupons.is_active'))
                    ->trueLabel(__('coupons.active_only'))
                    ->falseLabel(__('coupons.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_public')
                    ->trueLabel(__('coupons.public_only'))
                    ->falseLabel(__('coupons.private_only'))
                    ->native(false),
                TernaryFilter::make('is_auto_apply')
                    ->trueLabel(__('coupons.auto_apply_only'))
                    ->falseLabel(__('coupons.manual_apply_only'))
                    ->native(false),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label(__('Export'))
                    ->exports(self::getCouponExportPresets()),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('toggle_active')
                    ->label(fn (?Coupon $record): string => $record && $record->is_active ? __('coupons.deactivate') : __('coupons.activate'))
                    ->icon(fn (?Coupon $record): string => $record && $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (?Coupon $record): string => $record && $record->is_active ? 'warning' : 'success')
                    ->action(function (Coupon $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('coupons.activated_successfully') : __('coupons.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label(__('coupons.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (Coupon $record): void {
                        $newCoupon = $record->replicate();
                        $newCoupon->code = $record->code . '_copy_' . time();
                        $newCoupon->name = $record->name . ' (Copy)';
                        $newCoupon->used_count = 0;
                        $newCoupon->save();

                        Notification::make()
                            ->title(__('coupons.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label(__('Export selected'))
                        ->exports(self::getCouponExportPresets()),
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('coupons.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('coupons.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('coupons.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('coupons.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['customerGroup:id,name']);
    }

    /**
     * @return array<int, ExcelExport>
     */
    private static function getCouponExportPresets(): array
    {
        return [
            ExcelExport::make('coupon_report')
                ->fromTable()
                ->queue()
                ->withChunkSize(500)
                ->withColumns([
                    Column::make('code')
                        ->heading(__('coupons.code')),
                    Column::make('type')
                        ->heading(__('coupons.type')),
                    Column::make('value')
                        ->heading(__('coupons.value'))
                        ->format(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE)
                        ->formatStateUsing(
                            fn ($state, Coupon $record): string => match ($record->type) {
                                'percentage'    => $state === null ? '' : sprintf('%s%%', $state),
                                'free_shipping' => __('coupons.free_shipping'),
                                default         => $state === null ? '' : (string) $state,
                            }
                        ),
                    Column::make('starts_at')
                        ->heading(__('coupons.starts_at')),
                    Column::make('expires_at')
                        ->heading(__('coupons.ends_at')),
                ]),
        ];
    }

    /**
     * @return array<int, ExcelExport>
     */
    private static function getCouponExportPresets(): array
    {
        return [
            ExcelExport::make('coupon_report')
                ->fromTable()
                ->queue()
                ->withChunkSize(500)
                ->withColumns([
                    Column::make('code')
                        ->heading(__('coupons.code')),
                    Column::make('type')
                        ->heading(__('coupons.type')),
                    Column::make('value')
                        ->heading(__('coupons.value'))
                        ->format(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE)
                        ->formatStateUsing(
                            fn ($state, Coupon $record): string => match ($record->type) {
                                'percentage' => $state === null ? '' : sprintf('%s%%', $state),
                                'free_shipping' => __('coupons.free_shipping'),
                                default => $state === null ? '' : (string) $state,
                            }
                        ),
                    Column::make('starts_at')
                        ->heading(__('coupons.starts_at')),
                    Column::make('expires_at')
                        ->heading(__('coupons.ends_at')),
                ]),
        ];
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
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'view'   => Pages\ViewCoupon::route('/{record}'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
