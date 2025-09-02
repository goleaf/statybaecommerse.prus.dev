<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountPresetController extends Controller
{
    public function index(): View
    {
        $this->applyPreferredLocale();
        $categories = DB::table('sh_categories')->select('id', 'name', 'slug')->orderBy('name')->limit(200)->get();
        return view('livewire.admin.discounts.presets', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->applyPreferredLocale();
        $data = $request->validate([
            'preset' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'numeric'],
            'category_id' => ['nullable', 'integer'],
            'threshold' => ['nullable', 'numeric'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $discountId = DB::transaction(function () use ($data) {
            $now = now();
            $base = [
                'status' => 'active',
                'stacking_policy' => 'stack',
                'priority' => 100,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            switch ($data['preset']) {
                case 'sitewide_percent':
                    $id = DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'percentage',
                        'value' => (float) $data['value'],
                    ]));
                    return $id;
                case 'sitewide_fixed':
                    $id = DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'fixed',
                        'value' => (float) $data['value'],
                    ]));
                    return $id;
                case 'category_percent':
                    $id = DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'percentage',
                        'value' => (float) $data['value'],
                    ]));
                    DB::table('sh_discount_conditions')->insert([
                        'discount_id' => $id,
                        'type' => 'category',
                        'operator' => 'equals_to',
                        'value' => json_encode([(int) $data['category_id']]),
                        'position' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    return $id;
                case 'free_shipping_over':
                    $id = DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'fixed',
                        'value' => 0,
                        'applies_to_shipping' => true,
                    ]));
                    DB::table('sh_discounts')->where('id', $id)->update(['metadata' => json_encode(['shipping_cap_amount' => 0])]);
                    DB::table('sh_discount_conditions')->insert([
                        'discount_id' => $id,
                        'type' => 'cart_total',
                        'operator' => 'greater_than',
                        'value' => (string) ((float) $data['threshold']),
                        'position' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    return $id;
                case 'first_order_fixed':
                    $id = DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'fixed',
                        'value' => (float) $data['value'],
                        'first_order_only' => true,
                    ]));
                    return $id;
                case 'bogo':
                    $id = DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'bogo',
                        'value' => 0,
                        'metadata' => json_encode([
                            'buy_quantity' => (int) ($data['threshold'] ?? 1),
                            'get_quantity' => 1,
                            'discount_type' => 'percentage',
                            'discount_value' => (float) ($data['value'] ?? 100),
                        ]),
                    ]));
                    return $id;
                case 'tiered_spend':
                    $id = DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'percentage',
                        'value' => (float) ($data['value'] ?? 10),
                        'metadata' => json_encode([
                            'tiers' => [
                                ['threshold' => (float) ($data['threshold'] ?? 100), 'value' => (float) ($data['value'] ?? 10)],
                            ],
                        ]),
                    ]));
                    DB::table('sh_discount_conditions')->insert([
                        'discount_id' => $id,
                        'type' => 'cart_total',
                        'operator' => 'greater_than',
                        'value' => (string) ((float) ($data['threshold'] ?? 100)),
                        'position' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    return $id;
            }
            return 0;
        });

        return redirect()->route('admin.discounts.presets')->with('status', __('Preset created with ID: :id', ['id' => $discountId]));
    }

    protected function applyPreferredLocale(): void
    {
        $preferred = request()->user()?->preferred_locale ?: request('locale');
        if (is_string($preferred) && $preferred !== '' && in_array($preferred, explode(',', (string) config('app.supported_locales', 'en')), true)) {
            \Illuminate\Support\Facades\App::setLocale($preferred);
        }
    }
}
