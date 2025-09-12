<?php declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

final class ZoneController extends Controller
{
    public function index(Request $request): View
    {
        $zones = Zone::active()
            ->enabled()
            ->with(['currency', 'countries'])
            ->withCount('countries')
            ->ordered()
            ->paginate(12);

        return view('frontend.zones.index', compact('zones'));
    }

    public function show(Zone $zone): View
    {
        $zone->load(['currency', 'countries', 'translations']);
        
        return view('frontend.zones.show', compact('zone'));
    }

    public function getZonesByCountry(Request $request): JsonResponse
    {
        $countryId = $request->get('country_id');
        
        if (!$countryId) {
            return response()->json(['zones' => []]);
        }

        $zones = Zone::active()
            ->enabled()
            ->whereHas('countries', function ($query) use ($countryId) {
                $query->where('countries.id', $countryId);
            })
            ->with(['currency'])
            ->ordered()
            ->get();

        return response()->json([
            'zones' => $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->translated_name,
                    'code' => $zone->code,
                    'type' => $zone->type,
                    'tax_rate' => $zone->tax_rate,
                    'shipping_rate' => $zone->shipping_rate,
                    'free_shipping_threshold' => $zone->free_shipping_threshold,
                    'currency' => $zone->currency->code ?? 'EUR',
                ];
            })
        ]);
    }

    public function calculateShipping(Request $request): JsonResponse
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'order_amount' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
        ]);

        $zone = Zone::findOrFail($request->zone_id);
        $orderAmount = (float) $request->order_amount;
        $weight = (float) ($request->weight ?? 0);

        $shippingCost = $zone->calculateShipping($weight, $orderAmount);
        $taxAmount = $zone->calculateTax($orderAmount);
        $hasFreeShipping = $zone->hasFreeShipping($orderAmount);

        return response()->json([
            'shipping_cost' => $shippingCost,
            'tax_amount' => $taxAmount,
            'has_free_shipping' => $hasFreeShipping,
            'total_with_tax' => $orderAmount + $taxAmount,
            'total_with_shipping' => $orderAmount + $shippingCost + $taxAmount,
            'currency' => $zone->currency->code ?? 'EUR',
        ]);
    }

    public function getDefaultZone(): JsonResponse
    {
        $zone = Zone::getDefaultZone();
        
        if (!$zone) {
            return response()->json(['zone' => null]);
        }

        return response()->json([
            'zone' => [
                'id' => $zone->id,
                'name' => $zone->translated_name,
                'code' => $zone->code,
                'type' => $zone->type,
                'tax_rate' => $zone->tax_rate,
                'shipping_rate' => $zone->shipping_rate,
                'free_shipping_threshold' => $zone->free_shipping_threshold,
                'currency' => $zone->currency->code ?? 'EUR',
            ]
        ]);
    }
}
