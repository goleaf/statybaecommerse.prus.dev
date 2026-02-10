<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Currency;
use App\Models\PriceList;
use App\Models\PriceListItem;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * HasProductPricing
 *
 * Trait providing reusable functionality for product pricing calculations using Eloquent models.
 */
trait HasProductPricing
{
    /**
     * Get the effective price data for the product based on user context and current currency.
     */
    public function getPrice(): ?PriceData
    {
        $currency = current_currency_model();

        // Retrieve base price using Eloquent relationship
        $basePrice = $this->prices()
            ->where('currency_id', $currency->id)
            ->first();

        if (! $basePrice) {
            return null;
        }

        $value = (float) $basePrice->amount;
        $compare = null;

        // Apply price lists (B2B/group/partner net pricing) if available
        try {
            $user = Auth::user();

            if ($user) {
                // Find applicable price lists using Eloquent query builder
                $priceList = PriceList::query()
                    ->enabled()
                    ->active()
                    ->where('currency_id', $currency->id)
                    ->where(function ($query) use ($user) {
                        $query->whereHas('customerGroups', function ($q) use ($user) {
                            $q->whereHas('users', fn ($sq) => $sq->where('user_id', $user->id));
                        })->orWhereHas('partners', function ($q) use ($user) {
                            $q->whereHas('users', fn ($sq) => $sq->where('user_id', $user->id));
                        });
                    })
                    ->orderBy('priority')
                    ->first();

                if ($priceList) {
                    // Check for a specific item price in the selected price list
                    $priceItem = PriceListItem::query()
                        ->where('price_list_id', $priceList->id)
                        ->where('product_id', $this->id)
                        ->first();

                    if ($priceItem && $priceItem->net_amount !== null) {
                        $value = (float) $priceItem->net_amount;
                    }
                }
            }
        } catch (Throwable $e) {
            // Fallback silently if any error occurs during specialized price lookup
        }

        return new PriceData(
            value: $value,
            compare: $compare,
            percentage: ($compare && $compare > 0) ? (float) round(($compare - $value) / $compare * 100) : null
        );
    }
}
