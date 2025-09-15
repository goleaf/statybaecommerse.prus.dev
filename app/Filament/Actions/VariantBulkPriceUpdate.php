<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class VariantBulkPriceUpdate extends Action
{
    public static function make(): static
    {
        return parent::make('bulk_price_update')
            ->label(__('product_variants.actions.bulk_price_update'))
            ->icon('heroicon-o-currency-euro')
            ->color('warning')
            ->form([
                Select::make('price_type')
                    ->label(__('product_variants.fields.price_type'))
                    ->options([
                        'price' => __('product_variants.price_types.regular'),
                        'wholesale_price' => __('product_variants.price_types.wholesale'),
                        'member_price' => __('product_variants.price_types.member'),
                        'promotional_price' => __('product_variants.price_types.promotional'),
                    ])
                    ->required()
                    ->default('price'),
                
                Select::make('update_type')
                    ->label(__('product_variants.fields.update_type'))
                    ->options([
                        'fixed_amount' => __('product_variants.update_types.fixed_amount'),
                        'percentage' => __('product_variants.update_types.percentage'),
                        'multiply_by' => __('product_variants.update_types.multiply_by'),
                        'set_to' => __('product_variants.update_types.set_to'),
                    ])
                    ->required()
                    ->default('percentage'),
                
                TextInput::make('update_value')
                    ->label(__('product_variants.fields.update_value'))
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->helperText(__('product_variants.help.update_value')),
                
                Toggle::make('apply_to_sale_items')
                    ->label(__('product_variants.fields.apply_to_sale_items'))
                    ->default(true),
                
                Toggle::make('update_compare_price')
                    ->label(__('product_variants.fields.update_compare_price'))
                    ->default(false),
                
                Select::make('compare_price_action')
                    ->label(__('product_variants.fields.compare_price_action'))
                    ->options([
                        'no_change' => __('product_variants.compare_price_actions.no_change'),
                        'match_new_price' => __('product_variants.compare_price_actions.match_new_price'),
                        'increase_by_percentage' => __('product_variants.compare_price_actions.increase_by_percentage'),
                        'increase_by_fixed_amount' => __('product_variants.compare_price_actions.increase_by_fixed_amount'),
                    ])
                    ->default('no_change')
                    ->visible(fn (callable $get) => $get('update_compare_price')),
                
                TextInput::make('compare_price_value')
                    ->label(__('product_variants.fields.compare_price_value'))
                    ->numeric()
                    ->step(0.01)
                    ->visible(fn (callable $get) => $get('update_compare_price') && in_array($get('compare_price_action'), ['increase_by_percentage', 'increase_by_fixed_amount'])),
                
                Toggle::make('set_sale_period')
                    ->label(__('product_variants.fields.set_sale_period'))
                    ->default(false),
                
                DateTimePicker::make('sale_start_date')
                    ->label(__('product_variants.fields.sale_start_date'))
                    ->visible(fn (callable $get) => $get('set_sale_period'))
                    ->default(now()),
                
                DateTimePicker::make('sale_end_date')
                    ->label(__('product_variants.fields.sale_end_date'))
                    ->visible(fn (callable $get) => $get('set_sale_period'))
                    ->default(now()->addDays(30)),
                
                Textarea::make('change_reason')
                    ->label(__('product_variants.fields.change_reason'))
                    ->maxLength(500)
                    ->rows(3)
                    ->placeholder(__('product_variants.placeholders.change_reason')),
            ])
            ->action(function (array $data, Collection $records): void {
                DB::transaction(function () use ($data, $records): void {
                    $updatedCount = 0;
                    $skippedCount = 0;
                    
                    foreach ($records as $record) {
                        /** @var ProductVariant $record */
                        
                        // Skip sale items if not applying to them
                        if (!$data['apply_to_sale_items'] && $record->is_on_sale) {
                            $skippedCount++;
                            continue;
                        }
                        
                        $oldPrice = $record->{$data['price_type']} ?? 0;
                        $newPrice = $oldPrice;
                        
                        // Calculate new price based on update type
                        switch ($data['update_type']) {
                            case 'fixed_amount':
                                $newPrice = $oldPrice + (float) $data['update_value'];
                                break;
                            case 'percentage':
                                $newPrice = $oldPrice * (1 + ((float) $data['update_value'] / 100));
                                break;
                            case 'multiply_by':
                                $newPrice = $oldPrice * (float) $data['update_value'];
                                break;
                            case 'set_to':
                                $newPrice = (float) $data['update_value'];
                                break;
                        }
                        
                        // Ensure price is not negative
                        $newPrice = max(0, $newPrice);
                        
                        // Update the price
                        $record->{$data['price_type']} = $newPrice;
                        
                        // Update compare price if requested
                        if ($data['update_compare_price']) {
                            switch ($data['compare_price_action']) {
                                case 'match_new_price':
                                    $record->compare_price = $newPrice;
                                    break;
                                case 'increase_by_percentage':
                                    if ($data['compare_price_value']) {
                                        $record->compare_price = $newPrice * (1 + ((float) $data['compare_price_value'] / 100));
                                    }
                                    break;
                                case 'increase_by_fixed_amount':
                                    if ($data['compare_price_value']) {
                                        $record->compare_price = $newPrice + (float) $data['compare_price_value'];
                                    }
                                    break;
                            }
                        }
                        
                        // Set sale period if requested
                        if ($data['set_sale_period']) {
                            $record->is_on_sale = true;
                            $record->sale_start_date = $data['sale_start_date'];
                            $record->sale_end_date = $data['sale_end_date'];
                        }
                        
                        $record->save();
                        
                        // Record price change history
                        $record->recordPriceChange(
                            $oldPrice,
                            $newPrice,
                            $data['price_type'],
                            $data['change_reason'] ?? 'Bulk price update',
                            auth()->id(),
                            $data['sale_start_date'] ?? null,
                            $data['sale_end_date'] ?? null
                        );
                        
                        $updatedCount++;
                    }
                    
                    // Send notification
                    Notification::make()
                        ->title(__('product_variants.notifications.bulk_update_success'))
                        ->body(__('product_variants.notifications.bulk_update_success_body', [
                            'updated' => $updatedCount,
                            'skipped' => $skippedCount
                        ]))
                        ->success()
                        ->send();
                });
            });
    }
}