<?php

namespace App\Services\Discounts;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DiscountEngine
{
    public function evaluate(array $context): array
    {
        // Context keys: zone_id, currency_code, channel_id, user_id, partner_tier, group_ids, now
        // cart: items [ [product_id, variant_id, quantity, unit_price], ... ] and subtotal
        $now = $context['now'] ?? now();

        $candidateDiscounts = $this->collectCandidates($context, $now);
        $eligibleDiscounts = $this->filterEligibility($candidateDiscounts, $context, $now);
        $calculated = $this->computeEffects($eligibleDiscounts, $context);
        $final = $this->applyStackingAndPriority($calculated, $context);

        // Debug logging
        if (app()->bound('debugbar.discount')) {
            $collector = app('debugbar.discount');
            foreach ($final as $discount) {
                $collector->logDiscountApplication(
                    $discount['code'] ?? 'unknown',
                    $context,
                    true,
                    $discount['amount'] ?? 0
                );
            }
        }

        return $final;
    }

    protected function collectCandidates(array $context, $now): Collection
    {
        // Minimal placeholder: fetch active discounts by date/status and basic restrictions
        $cacheKey = sprintf(
            'discount:candidates:%s:%s:%s:%s:%s:%s',
            $context['zone_id'] ?? 'na',
            $context['currency_code'] ?? 'na',
            $context['channel_id'] ?? 'na',
            md5(json_encode($context['group_ids'] ?? [])),
            ($context['partner_tier'] ?? 'na'),
            $now->format('YmdHi')
        );

        // Check if cache store supports tagging
        $cacheStore = Cache::getStore();
        $supportsTags = method_exists($cacheStore, 'tags');
        
        if ($supportsTags) {
            $cached = Cache::tags(['discounts'])->get($cacheKey);
            $isHit = $cached !== null;

            // Debug cache operation
            if (app()->bound('debugbar.discount') && method_exists(app('debugbar.discount'), 'logCacheOperation')) {
                app('debugbar.discount')->logCacheOperation($cacheKey, $isHit, $cached);
            }

            return Cache::tags(['discounts'])->remember($cacheKey, now()->addMinutes(3), function () use ($now) {
                return collect(DB::table('sh_discounts')
                    ->where('status', 'active')
                    ->where(function ($q) use ($now) {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                    })
                    ->orderBy('priority')
                    ->get());
            });
        } else {
            // Fallback for cache stores that don't support tagging
            $cached = Cache::get($cacheKey);
            $isHit = $cached !== null;

            // Debug cache operation
            if (app()->bound('debugbar.discount') && method_exists(app('debugbar.discount'), 'logCacheOperation')) {
                app('debugbar.discount')->logCacheOperation($cacheKey, $isHit, $cached);
            }

            return Cache::remember($cacheKey, now()->addMinutes(3), function () use ($now) {
                return collect(DB::table('sh_discounts')
                    ->where('status', 'active')
                    ->where(function ($q) use ($now) {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                    })
                    ->orderBy('priority')
                    ->get());
            });
        }
    }

    protected function filterEligibility(Collection $discounts, array $context, $now): Collection
    {
        $currency = data_get($context, 'currency_code');
        $channelId = data_get($context, 'channel_id');
        $zoneId = data_get($context, 'zone_id');
        $userId = data_get($context, 'user_id');
        $groupIds = collect(data_get($context, 'group_ids', []))->map(fn ($v) => (int) $v)->filter()->values();
        if ($groupIds->isEmpty() && $userId) {
            $groupIds = DB::table('customer_group_user')->where('user_id', $userId)->pluck('group_id');
        }
        $partnerTier = data_get($context, 'partner_tier');
        if (! $partnerTier && $userId) {
            $partnerTier = DB::table('partner_users as pu')
                ->join('partners as p', 'p.id', '=', 'pu.partner_id')
                ->where('pu.user_id', $userId)
                ->value('p.tier');
        }

        // Honor weekday/time windows, currency/channel/zone restrictions; first order; customer groups; per-day limit
        return $discounts->filter(function ($d) use ($now, $currency, $channelId, $zoneId, $userId, $groupIds, $partnerTier) {
            if (! empty($d->weekday_mask)) {
                $allowed = collect(explode(',', $d->weekday_mask))->filter()->map(fn ($v) => (int) $v);
                if (! $allowed->contains((int) $now->dayOfWeekIso)) {
                    return false;
                }
            }
            // Per-day throttling
            if (! empty($d->per_day_limit)) {
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $countToday = DB::table('discount_redemptions')
                    ->where('discount_id', $d->id)
                    ->whereBetween('redeemed_at', [$start, $end])
                    ->count();
                if ($countToday >= (int) $d->per_day_limit) {
                    return false;
                }
            }
            if (! empty($d->time_window)) {
                $tw = is_string($d->time_window) ? json_decode($d->time_window, true) : (array) $d->time_window;
                if (! empty($tw['start']) && ! empty($tw['end'])) {
                    $tz = $tw['tz'] ?? 'UTC';
                    $start = $now->copy()->setTimezone($tz)->format('H:i');
                    if ($start < $tw['start'] || $start > $tw['end']) {
                        return false;
                    }
                }
            }
            if (! empty($d->currency_restrictions)) {
                $list = is_string($d->currency_restrictions) ? json_decode($d->currency_restrictions, true) : (array) $d->currency_restrictions;
                if ($currency && ! in_array($currency, $list, true)) {
                    return false;
                }
            }
            if (! empty($d->channel_restrictions)) {
                $list = is_string($d->channel_restrictions) ? json_decode($d->channel_restrictions, true) : (array) $d->channel_restrictions;
                if ($channelId && ! in_array($channelId, $list, true)) {
                    return false;
                }
            }
            if (! empty($d->first_order_only) && $userId) {
                $hasOrder = DB::table('sh_orders')->where('customer_id', $userId)->whereIn('status', ['placed', 'paid', 'fulfilled', 'completed'])->exists();
                if ($hasOrder) {
                    return false;
                }
            }
            // Groups condition: if present, require intersection
            $hasGroupCondition = DB::table('discount_conditions')->where('discount_id', $d->id)->where('type', 'customer_group')->exists();
            if ($hasGroupCondition) {
                $values = DB::table('discount_conditions')->where('discount_id', $d->id)->where('type', 'customer_group')->pluck('value');
                $allowed = collect($values)->map(function ($v) {
                    $arr = is_string($v) ? json_decode($v, true) : (array) $v;

                    return collect($arr)->map(fn ($x) => (int) $x);
                })->flatten()->filter()->values();
                if ($allowed->isNotEmpty() && $groupIds->intersect($allowed)->isEmpty()) {
                    return false;
                }
            }
            // Zone condition: if present, require zone id match
            $hasZoneCondition = DB::table('discount_conditions')->where('discount_id', $d->id)->where('type', 'zone')->exists();
            if ($hasZoneCondition) {
                $values = DB::table('discount_conditions')->where('discount_id', $d->id)->where('type', 'zone')->pluck('value');
                $allowedZoneIds = collect($values)->map(function ($v) {
                    $arr = is_string($v) ? json_decode($v, true) : (array) $v;

                    return collect($arr)->map(fn ($x) => (int) $x);
                })->flatten()->filter()->values();
                if ($allowedZoneIds->isNotEmpty() && $zoneId && ! $allowedZoneIds->contains((int) $zoneId)) {
                    return false;
                }
            }
            // User condition: if present, require explicit user id
            $hasUserCondition = DB::table('discount_conditions')->where('discount_id', $d->id)->where('type', 'user')->exists();
            if ($hasUserCondition && $userId) {
                $values = DB::table('discount_conditions')->where('discount_id', $d->id)->where('type', 'user')->pluck('value');
                $allowedUserIds = collect($values)->map(function ($v) {
                    $arr = is_string($v) ? json_decode($v, true) : (array) $v;

                    return collect($arr)->map(fn ($x) => (int) $x);
                })->flatten()->filter()->values();
                if ($allowedUserIds->isNotEmpty() && ! $allowedUserIds->contains((int) $userId)) {
                    return false;
                }
            }
            // Partner tier condition
            $hasPartnerTierCondition = DB::table('discount_conditions')->where('discount_id', $d->id)->where('type', 'partner_tier')->exists();
            if ($hasPartnerTierCondition) {
                $values = DB::table('discount_conditions')->where('discount_id', $d->id)->where('type', 'partner_tier')->pluck('value');
                $allowedTiers = collect($values)->map(function ($v) {
                    $arr = is_string($v) ? json_decode($v, true) : (array) $v;

                    return collect($arr)->map(fn ($x) => (string) $x);
                })->flatten()->filter()->values()->map(fn ($v) => strtolower($v));
                if ($allowedTiers->isNotEmpty() && strtolower((string) $partnerTier) !== '' && ! $allowedTiers->contains(strtolower((string) $partnerTier))) {
                    return false;
                }
            }

            // zone-based via scope json in scope or via explicit pivot (out of scope here)
            return true;
        });
    }

    protected function computeEffects(Collection $eligible, array $context): array
    {
        $subtotal = (float) data_get($context, 'cart.subtotal', 0);
        $shippingBase = (float) data_get($context, 'shipping.base_amount', 0);
        $code = strtoupper((string) data_get($context, 'code'));
        $items = collect(data_get($context, 'cart.items', []));

        // Preload product attributes for scoping
        $productIds = $items->pluck('product_id')->filter()->unique()->values();
        $productToBrand = DB::table('products')->whereIn('id', $productIds)->pluck('brand_id', 'id');
        $productToCategories = DB::table('product_categories')->whereIn('product_id', $productIds)->get()->groupBy('product_id')->map(fn ($rows) => collect($rows)->pluck('category_id')->values());

        $discountAmount = 0.0;
        $shippingDiscount = 0.0;
        $applied = [];
        $lineDiscounts = [];
        $cartDiscounts = [];

        foreach ($eligible as $d) {
            // If a code is provided, require a matching code record for this discount
            if ($code) {
                $hasCode = DB::table('sh_discount_codes')
                    ->where('discount_id', $d->id)
                    ->whereRaw('UPPER(code) = ?', [$code])
                    ->exists();
                if (! $hasCode) {
                    continue;
                }
            }

            // Load conditions
            $conditions = DB::table('discount_conditions')->where('discount_id', $d->id)->get();
            $lineScopeTypes = ['product', 'category', 'brand', 'collection', 'attribute_value'];
            $hasLineScope = $conditions->whereIn('type', $lineScopeTypes)->isNotEmpty();
            $cartTotalCond = $conditions->firstWhere('type', 'cart_total');
            $itemQtyCond = $conditions->firstWhere('type', 'item_qty');

            // Threshold checks
            if ($cartTotalCond && ! $this->compareOperator($subtotal, $cartTotalCond->operator, $cartTotalCond->value)) {
                continue;
            }
            $totalQty = (int) $items->sum('quantity');
            if ($itemQtyCond && ! $this->compareOperator($totalQty, $itemQtyCond->operator, $itemQtyCond->value)) {
                continue;
            }

            $amount = 0.0;
            if ($hasLineScope && $items->isNotEmpty()) {
                // Apply per matching item
                $matchedAny = false;
                foreach ($items as $idx => $it) {
                    $matches = $this->itemMatches($it, $conditions, $productToBrand, $productToCategories);
                    if (! $matches) {
                        continue;
                    }
                    $matchedAny = true;
                    $lineTotal = (float) $it['unit_price'] * (int) $it['quantity'];
                    $lineAmount = 0.0;
                    if ($d->type === 'percentage') {
                        $lineAmount = round($lineTotal * ((float) $d->value / 100), 2);
                    } elseif ($d->type === 'fixed') {
                        // fixed per line total, capped
                        $lineAmount = min($lineTotal, (float) $d->value);
                    } elseif ($d->type === 'bogo') {
                        // BOGO handled after scanning all matched items; skip here
                        $lineAmount = 0.0;
                    }
                    if ($lineAmount > 0) {
                        $lineDiscounts[] = ['item_index' => $idx, 'amount' => $lineAmount, 'discount_id' => $d->id];
                        $amount += $lineAmount;
                    }
                }
                // BOGO calculation across matched items: metadata {buy_qty:int, get_qty:int, percent_off?:float}
                if ($d->type === 'bogo' && $matchedAny) {
                    $meta = is_string($d->metadata ?? null) ? json_decode($d->metadata, true) : (array) ($d->metadata ?? []);
                    $buyQty = max(1, (int) ($meta['buy_qty'] ?? 1));
                    $getQty = max(1, (int) ($meta['get_qty'] ?? 1));
                    $percentOff = isset($meta['percent_off']) ? (float) $meta['percent_off'] : 100.0;
                    // Build price list of matched units
                    $unitPrices = [];
                    foreach ($items as $it) {
                        $matches = $this->itemMatches($it, $conditions, $productToBrand, $productToCategories);
                        if (! $matches) {
                            continue;
                        }
                        $q = (int) $it['quantity'];
                        for ($i = 0; $i < $q; $i++) {
                            $unitPrices[] = (float) $it['unit_price'];
                        }
                    }
                    if (! empty($unitPrices)) {
                        sort($unitPrices);  // ascending for cheapest free
                        $setSize = $buyQty + $getQty;
                        $freeUnits = intdiv(count($unitPrices), $setSize) * $getQty;
                        $freeUnits = max(0, $freeUnits);
                        $bogoAmount = 0.0;
                        for ($i = 0; $i < $freeUnits; $i++) {
                            $bogoAmount += round($unitPrices[$i] * ($percentOff / 100.0), 2);
                        }
                        if ($bogoAmount > 0) {
                            $amount += $bogoAmount;
                            $applied[] = ['id' => $d->id, 'amount' => $bogoAmount];
                        }
                    }
                }
                if (! $matchedAny) {
                    continue;
                }
            } else {
                // Cart-level discount
                if ($d->type === 'percentage') {
                    $amount = round($subtotal * ((float) $d->value / 100), 2);
                } elseif ($d->type === 'fixed') {
                    $amount = (float) $d->value;
                } elseif ($d->type === 'bogo') {
                    // If no line scope, consider entire cart for BOGO
                    $meta = is_string($d->metadata ?? null) ? json_decode($d->metadata, true) : (array) ($d->metadata ?? []);
                    $buyQty = max(1, (int) ($meta['buy_qty'] ?? 1));
                    $getQty = max(1, (int) ($meta['get_qty'] ?? 1));
                    $percentOff = isset($meta['percent_off']) ? (float) $meta['percent_off'] : 100.0;
                    $unitPrices = [];
                    foreach ($items as $it) {
                        $q = (int) $it['quantity'];
                        for ($i = 0; $i < $q; $i++) {
                            $unitPrices[] = (float) $it['unit_price'];
                        }
                    }
                    if (! empty($unitPrices)) {
                        sort($unitPrices);
                        $freeUnits = intdiv(count($unitPrices), $buyQty + $getQty) * $getQty;
                        $bogoAmount = 0.0;
                        for ($i = 0; $i < $freeUnits; $i++) {
                            $bogoAmount += round($unitPrices[$i] * ($percentOff / 100.0), 2);
                        }
                        $amount = $bogoAmount;
                    }
                }
                if ($amount > 0) {
                    $cartDiscounts[] = ['amount' => $amount, 'discount_id' => $d->id];
                }
            }

            if ($amount > 0) {
                $discountAmount += $amount;
                $applied[] = ['id' => $d->id, 'amount' => $amount];
            }

            if (! empty($d->free_shipping) || ! empty($d->applies_to_shipping)) {
                // Optional cap from metadata: {"shipping_cap_amount": 4.99}
                $meta = is_string($d->metadata ?? null) ? json_decode($d->metadata, true) : (array) ($d->metadata ?? []);
                $cap = isset($meta['shipping_cap_amount']) ? (float) $meta['shipping_cap_amount'] : null;
                if (! empty($d->free_shipping)) {
                    $shippingDiscount = max($shippingDiscount, $shippingBase);
                } else {
                    if ($cap !== null) {
                        // Cap shipping to a fixed amount when base is higher
                        if ($shippingBase > $cap) {
                            $shippingDiscount = max($shippingDiscount, round($shippingBase - $cap, 2));
                        }
                    } else {
                        // Apply same percent/fixed to shipping as to cart if applies_to_shipping
                        if ($d->type === 'percentage') {
                            $shippingDiscount = max($shippingDiscount, round($shippingBase * ((float) $d->value / 100), 2));
                        } elseif ($d->type === 'fixed') {
                            $shippingDiscount = max($shippingDiscount, min($shippingBase, (float) $d->value));
                        }
                    }
                }
            }
        }

        return [
            'applied' => $applied,
            'discount_total_amount' => round($discountAmount, 2),
            'line_discounts' => $lineDiscounts,
            'cart_discounts' => $cartDiscounts,
            'shipping' => [
                'discount_amount' => round($shippingDiscount, 2),
            ],
        ];
    }

    protected function compareOperator($left, string $operator, $rawValue): bool
    {
        $value = is_numeric($rawValue) ? (float) $rawValue : (float) (is_string($rawValue) ? json_decode($rawValue, true) : $rawValue);

        return match ($operator) {
            'equals_to' => (float) $left == $value,
            'not_equals_to' => (float) $left != $value,
            'less_than' => (float) $left < $value,
            'greater_than' => (float) $left > $value,
            'starts_with', 'ends_with', 'contains', 'not_contains' => true,
            default => true,
        };
    }

    protected function itemMatches(array $item, Collection $conditions, $productToBrand, $productToCategories): bool
    {
        // Evaluate basic ANY semantics across provided scope conditions
        $productId = (int) ($item['product_id'] ?? 0);
        $brandId = (int) ($productToBrand[$productId] ?? 0);
        $categoryIds = collect($productToCategories[$productId] ?? [])->map(fn ($v) => (int) $v);

        foreach ($conditions as $cond) {
            $values = is_string($cond->value) ? json_decode($cond->value, true) : (array) $cond->value;
            switch ($cond->type) {
                case 'product':
                    if (in_array($productId, $values, true)) {
                        return true;
                    }
                    break;
                case 'brand':
                    if ($brandId && in_array($brandId, $values, true)) {
                        return true;
                    }
                    break;
                case 'category':
                    if ($categoryIds->intersect(collect($values)->map(fn ($v) => (int) $v))->isNotEmpty()) {
                        return true;
                    }
                    break;
                case 'collection':
                    // Simplified: treat as category-like; real impl would check pivot product_collections
                    $inCollection = DB::table('product_collections')->where('product_id', $productId)->whereIn('collection_id', $values)->exists();
                    if ($inCollection) {
                        return true;
                    }
                    break;
                case 'attribute_value':
                    // If variant attributes are available, check pivot product_variant_attributes; omitted for brevity
                    break;
            }
        }

        return false;
    }

    protected function applyStackingAndPriority(array $calculated, array $context): array
    {
        // Enforce stacking policy and exclusive
        $applied = collect($calculated['applied'] ?? []);
        if ($applied->isEmpty()) {
            return $calculated;
        }
        // If any exclusive discount applied, keep only that one with max amount
        // (requires discount record; re-fetch minimal info)
        $appliedWithFlags = $applied->map(function ($a) {
            $d = DB::table('sh_discounts')->select('id', 'exclusive', 'stacking_policy')->where('id', (int) $a['id'])->first();

            return array_merge($a, [
                'exclusive' => (bool) data_get($d, 'exclusive', false),
                'stacking_policy' => data_get($d, 'stacking_policy', 'stack'),
            ]);
        });
        $exclusive = $appliedWithFlags->firstWhere('exclusive', true);
        if ($exclusive) {
            $best = $appliedWithFlags->sortByDesc('amount')->first();
            $calculated['applied'] = [$best];
            $calculated['discount_total_amount'] = round((float) $best['amount'], 2);

            return $calculated;
        }
        // If single_best policy anywhere, keep only max amount
        if ($appliedWithFlags->contains(fn ($a) => ($a['stacking_policy'] ?? 'stack') === 'single_best')) {
            $best = $appliedWithFlags->sortByDesc('amount')->first();
            $calculated['applied'] = [$best];
            $calculated['discount_total_amount'] = round((float) $best['amount'], 2);
        }

        return $calculated;
    }
}
