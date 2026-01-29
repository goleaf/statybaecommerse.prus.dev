<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Forms\Components\Component as FormComponent;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class VariantBulkPriceUpdate extends Action
{
    private const FLATPICKR_COMPONENT = 'Coolsam\\FilamentFlatpickr\\Forms\\Components\\Flatpickr';

    public static function make(?string $name = null): static
    {
        // Respect a custom name when callers provide one while keeping the historical default intact.
        return parent::make($name ?? 'bulk_price_update')
            ->label(__('product.variants.actions.bulk_price_update'))
            ->icon('heroicon-o-currency-euro')
            ->color('warning')
            ->form([
                Select::make('price_type')
                    ->label(__('product.variants.fields.price_type'))
                    ->options([
                        'price'             => __('product.variants.price_types.regular'),
                        'wholesale_price'   => __('product.variants.price_types.wholesale'),
                        'member_price'      => __('product.variants.price_types.member'),
                        'promotional_price' => __('product.variants.price_types.promotional'),
                    ])
                    ->required()
                    ->default('price'),
                Select::make('update_type')
                    ->label(__('product.variants.fields.update_type'))
                    ->options([
                        'fixed_amount' => __('product.variants.update_types.fixed_amount'),
                        'percentage'   => __('product.variants.update_types.percentage'),
                        'multiply_by'  => __('product.variants.update_types.multiply_by'),
                        'set_to'       => __('product.variants.update_types.set_to'),
                    ])
                    ->required()
                    ->default('percentage'),
                TextInput::make('update_value')
                    ->label(__('product.variants.fields.update_value'))
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->helperText(__('product.variants.help.update_value')),
                Toggle::make('apply_to_sale_items')
                    ->label(__('product.variants.fields.apply_to_sale_items'))
                    ->default(true),
                Toggle::make('update_compare_price')
                    ->label(__('product.variants.fields.update_compare_price'))
                    ->default(false),
                Select::make('compare_price_action')
                    ->label(__('product.variants.fields.compare_price_action'))
                    ->options([
                        'no_change'                => __('product.variants.compare_price_actions.no_change'),
                        'match_new_price'          => __('product.variants.compare_price_actions.match_new_price'),
                        'increase_by_percentage'   => __('product.variants.compare_price_actions.increase_by_percentage'),
                        'increase_by_fixed_amount' => __('product.variants.compare_price_actions.increase_by_fixed_amount'),
                    ])
                    ->default('no_change')
                    ->visible(fn (Get $get): bool => (bool) $get('update_compare_price')),
                TextInput::make('compare_price_value')
                    ->label(__('product.variants.fields.compare_price_value'))
                    ->numeric()
                    ->step(0.01)
                    ->visible(
                        fn (Get $get): bool => (bool) $get('update_compare_price')
                            && in_array(
                                $get('compare_price_action'),
                                ['increase_by_percentage', 'increase_by_fixed_amount'],
                                true,
                            )
                    ),
                Toggle::make('set_sale_period')
                    ->label(__('product.variants.fields.set_sale_period'))
                    ->default(false),
                Section::make('sale_period')
                    ->label(__('product.variants.fields.set_sale_period'))
                    ->visible(fn (Get $get): bool => (bool) $get('set_sale_period'))
                    ->schema([
                        self::makeSalePeriodPicker(
                            name: 'sale_start_date',
                            label: __('product.variants.fields.sale_start_date'),
                            default: fn (): Carbon => now(),
                        ),
                        self::makeSalePeriodPicker(
                            name: 'sale_end_date',
                            label: __('product.variants.fields.sale_end_date'),
                            default: fn (): Carbon => now()->addDays(30),
                        ),
                    ]),
                Textarea::make('change_reason')
                    ->label(__('product.variants.fields.change_reason'))
                    ->maxLength(500)
                    ->rows(3)
                    ->placeholder(__('product.variants.placeholders.change_reason')),
            ])
            ->action(function (array $data, Collection $records): void {
                DB::transaction(function () use ($data, $records): void {
                    $updatedCount = 0;
                    $skippedCount = 0;
                    $rawReason = $data['change_reason'] ?? $data['reason'] ?? null;
                    $reason = is_string($rawReason) ? trim((string) $rawReason) : '';
                    if ($reason === '') {
                        $reason = 'Bulk price update';
                    }

                    foreach ($records as $record) {
                        /** @var ProductVariant $record */
                        $priceType = is_string($data['price_type'] ?? null) ? (string) $data['price_type'] : 'price';
                        $updateType = is_string($data['update_type'] ?? null) ? (string) $data['update_type'] : 'fixed_amount';
                        $updateValue = is_numeric($data['update_value'] ?? null)
                            ? (float) $data['update_value']
                            : 0.0;

                        // Skip sale items if not applying to them
                        $isSaleItem = (bool) $record->getAttribute('is_on_sale');
                        if (! ($data['apply_to_sale_items'] ?? false) && $isSaleItem) {
                            $skippedCount++;

                            continue;
                        }

                        $priceAttribute = $record->getAttribute($priceType);
                        $oldPrice = is_numeric($priceAttribute) ? (float) $priceAttribute : 0.0;
                        $newPrice = $oldPrice;

                        // Calculate new price based on update type
                        switch ($updateType) {
                            case 'fixed_amount':
                                $newPrice = $oldPrice + $updateValue;
                                break;
                            case 'percentage':
                                $newPrice = $oldPrice * (1 + ($updateValue / 100));
                                break;
                            case 'multiply_by':
                                $newPrice = $oldPrice * $updateValue;
                                break;
                            case 'set_to':
                                $newPrice = $updateValue;
                                break;
                        }

                        // Ensure price is not negative and persists with consistent precision
                        $newPrice = round(max(0, $newPrice), 2);

                        // Update the price
                        $record->forceFill([$priceType => $newPrice]);

                        // Update compare price if requested
                        if ($data['update_compare_price'] ?? false) {
                            $compareAction = is_string($data['compare_price_action'] ?? null)
                                ? (string) $data['compare_price_action']
                                : 'no_change';
                            $compareValue = is_numeric($data['compare_price_value'] ?? null)
                                ? (float) $data['compare_price_value']
                                : null;

                            switch ($compareAction) {
                                case 'match_new_price':
                                    $record->forceFill(['compare_price' => $newPrice]);
                                    break;
                                case 'increase_by_percentage':
                                    if ($compareValue !== null) {
                                        $adjusted = round($newPrice * (1 + ($compareValue / 100)), 2);
                                        $record->forceFill(['compare_price' => max(0, $adjusted)]);
                                    }
                                    break;
                                case 'increase_by_fixed_amount':
                                    if ($compareValue !== null) {
                                        $adjusted = round($newPrice + $compareValue, 2);
                                        $record->forceFill(['compare_price' => max(0, $adjusted)]);
                                    }
                                    break;
                            }
                        }

                        // Set sale period if requested
                        if ($data['set_sale_period'] ?? false) {
                            $record->forceFill([
                                'is_on_sale'      => true,
                                'sale_start_date' => $data['sale_start_date'] ?? null,
                                'sale_end_date'   => $data['sale_end_date'] ?? null,
                            ]);
                        }

                        $record->save();

                        $updatedCount++;
                    }

                    // Send notification
                    Notification::make()
                        ->title(__('product.variants.notifications.bulk_update_success'))
                        ->body(__('product.variants.notifications.bulk_update_success_body', [
                            'updated' => $updatedCount,
                            'skipped' => $skippedCount,
                        ]))
                        ->success()
                        ->send();
                });
            });
    }

    /**
     * Build a sale period date picker using Flatpickr when available, falling back to Filament's DateTimePicker otherwise.
     *
     * @param Closure(): Carbon $default
     */
    private static function makeSalePeriodPicker(string $name, string $label, Closure $default): FormComponent
    {
        $componentClass = class_exists(self::FLATPICKR_COMPONENT)
            ? self::FLATPICKR_COMPONENT
            : DateTimePicker::class;

        /** @var FormComponent $component */
        $component = $componentClass::make($name)
            ->label($label)
            ->default($default);

        if ($componentClass === self::FLATPICKR_COMPONENT) {
            /** @var mixed $component */
            $component = $component
                ->time(true)
                ->time24hr(true)
                ->seconds(false)
                ->format('Y-m-d H:i');

            return $component;
        }

        /** @var DateTimePicker $component */
        $component = $component
            ->time()
            ->seconds(false)
            ->format('Y-m-d H:i')
            ->displayFormat('Y-m-d H:i');

        return $component;
    }
}
