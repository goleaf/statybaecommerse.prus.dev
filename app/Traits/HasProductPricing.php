<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\DB;

// Using native Laravel helpers and custom PriceData DTO

/**
 * HasProductPricing
 *
 * Trait providing reusable functionality across multiple classes.
 */
trait HasProductPricing
{
    public function getPrice(): ?PriceData
    {
        $currencyCode = current_currency();
        $basePrice = $this->loadMissing('prices', 'prices.currency')->prices->reject(fn ($price) => $price->currency->code !== $currencyCode)->first();
        if (! $basePrice) {
            return null;
        }
        $value = (float) $basePrice->amount;
        $compare = $basePrice->compare_amount ? (float) $basePrice->compare_amount : null;
        // Apply price lists (B2B/group/partner net pricing) if available
        try {
            $currencyId = DB::table('currencies')->where('code', $currencyCode)->value('id');
            $userId = optional(auth()->user())->id;
            $candidate = DB::table('price_lists as pl')->where('pl.is_enabled', true)->where('pl.currency_id', $currencyId)->where(function ($q) use ($userId) {
                $q->whereExists(function ($sq) use ($userId) {
                    $sq->select(DB::raw(1))->from('group_price_list as gpl')->join('customer_group_user as cgu', 'cgu.group_id', '=', 'gpl.group_id')->whereColumn('gpl.price_list_id', 'pl.id')->where('cgu.user_id', $userId);
                })->orWhereExists(function ($sq) use ($userId) {
                    $sq->select(DB::raw(1))->from('partner_price_list as ppl')->join('partner_users as pu', 'pu.partner_id', '=', 'ppl.partner_id')->whereColumn('ppl.price_list_id', 'pl.id')->where('pu.user_id', $userId);
                });
            })->orderBy('pl.priority')->first();
            if ($candidate) {
                $net = DB::table('price_list_items')->where('price_list_id', $candidate->id)->where('product_id', $this->id)->value('net_amount');
                if ($net !== null) {
                    $value = (float) $net;
                }
            }
        } catch (\Throwable $e) {
            // Fallback silently to base price if price list tables missing
        }

        return new PriceData(value: $value, compare: $compare, percentage: $compare && $compare > 0 ? round(($compare - $value) / $compare * 100) : null);
    }
}
