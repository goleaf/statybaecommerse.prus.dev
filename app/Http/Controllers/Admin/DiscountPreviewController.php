<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Discounts\DiscountEngine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountPreviewController extends Controller
{
    public function show(int $discountId): View
    {
        $discount = DB::table('sh_discounts')->where('id', $discountId)->first();
        abort_unless($discount, 404);
        $currencies = DB::table('sh_currencies')->where('is_enabled', 1)->pluck('code');
        $zones = DB::table('sh_zones')->where('is_enabled', 1)->pluck('name', 'id');
        return view('livewire.admin.discounts.preview', [
            'discount' => $discount,
            'currencies' => $currencies,
            'zones' => $zones,
            'result' => null,
        ]);
    }

    public function compute(Request $request, int $discountId, DiscountEngine $engine): View|RedirectResponse
    {
        $discount = DB::table('sh_discounts')->where('id', $discountId)->first();
        abort_unless($discount, 404);

        $data = $request->validate([
            'currency_code' => ['required', 'string', 'size:3'],
            'zone_id' => ['nullable', 'integer'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'code' => ['nullable', 'string', 'max:64'],
            'items' => ['nullable', 'string'],  // CSV product_id:qty:unit_price
        ]);

        $items = [];
        if (!empty($data['items'])) {
            foreach (explode(',', $data['items']) as $row) {
                $parts = array_map('trim', explode(':', $row));
                if (count($parts) >= 3) {
                    $items[] = [
                        'product_id' => (int) $parts[0],
                        'variant_id' => null,
                        'quantity' => (int) $parts[1],
                        'unit_price' => (float) $parts[2],
                    ];
                }
            }
        }

        $result = $engine->evaluate([
            'zone_id' => $data['zone_id'] ?? null,
            'currency_code' => strtoupper($data['currency_code']),
            'channel_id' => null,
            'user_id' => auth()->id(),
            'now' => now(),
            'code' => $data['code'] ?? null,
            'cart' => [
                'subtotal' => (float) $data['subtotal'],
                'items' => $items,
            ],
        ]);

        $currencies = DB::table('sh_currencies')->where('is_enabled', 1)->pluck('code');
        $zones = DB::table('sh_zones')->where('is_enabled', 1)->pluck('name', 'id');
        return view('livewire.admin.discounts.preview', [
            'discount' => $discount,
            'currencies' => $currencies,
            'zones' => $zones,
            'result' => $result,
            'input' => $data,
        ]);
    }
}
