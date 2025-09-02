<?php

declare(strict_types=1);

namespace App\Traits;

use App\DTO\PriceData;
use Shopper\Core\Helpers\Price;
use Illuminate\Support\Facades\DB;

trait HasProductPricing
{
    public function getPrice(): ?PriceData
    {
        $currencyCode = current_currency();
        $basePrice = $this->loadMissing('prices', 'prices.currency')
            ->prices
            ->reject(fn ($price) => $price->currency->code !== $currencyCode)
            ->first();

        if (! $basePrice) {
            return null;
        }

        $value = Price::from($basePrice->amount, $currencyCode);
        $compare = $basePrice->compare_amount ? Price::from($basePrice->compare_amount, $currencyCode) : null;

        // Apply price lists (B2B/group/partner net pricing) if available
        try {
            $currencyId = DB::table('sh_currencies')->where('code', $currencyCode)->value('id');
            $zoneId = session('zone.id');
            $userId = optional(auth()->user())->id;

            $candidate = DB::table('sh_price_lists as pl')
                ->where('pl.is_enabled', true)
                ->where('pl.currency_id', $currencyId)
                ->when($zoneId, fn ($q) => $q->where(function ($qq) use ($zoneId) {
                    $qq->whereNull('pl.zone_id')->orWhere('pl.zone_id', $zoneId);
                }))
                ->where(function ($q) use ($userId) {
                    $q->whereExists(function ($sq) use ($userId) {
                        $sq->select(DB::raw(1))
                            ->from('sh_group_price_list as gpl')
                            ->join('sh_customer_group_user as cgu', 'cgu.group_id', '=', 'gpl.group_id')
                            ->whereColumn('gpl.price_list_id', 'pl.id')
                            ->where('cgu.user_id', $userId);
                    })
                    ->orWhereExists(function ($sq) use ($userId) {
                        $sq->select(DB::raw(1))
                            ->from('sh_partner_price_list as ppl')
                            ->join('sh_partner_users as pu', 'pu.partner_id', '=', 'ppl.partner_id')
                            ->whereColumn('ppl.price_list_id', 'pl.id')
                            ->where('pu.user_id', $userId);
                    });
                })
                ->orderBy('pl.priority')
                ->first();

            if ($candidate) {
                $net = DB::table('sh_price_list_items')
                    ->where('price_list_id', $candidate->id)
                    ->where('product_id', $this->id)
                    ->value('net_amount');
                if ($net !== null) {
                    $value = Price::from((float) $net, $currencyCode);
                }
            }
        } catch (\Throwable $e) {
            // Fallback silently to base price if price list tables missing
        }

        return new PriceData(
            value: $value,
            compare: $compare,
            percentage: $compare && $compare->getAmount() > 0
                ? round((($compare->getAmount() - $value->getAmount()) / $compare->getAmount()) * 100)
                : null
        );
    }
}
