<?php declare(strict_types=1);

namespace App\Livewire\Admin\Discount;

use Illuminate\Support\Facades\App as AppFacade;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Presets extends Component
{
    public array $categories = [];

    public string $preset = '';

    public string $name = '';

    public ?float $value = null;

    public ?int $category_id = null;

    public ?float $threshold = null;

    public ?string $currency = null;

    public function mount(): void
    {
        $this->applyPreferredLocale();
        $this->categories = DB::table('sh_categories')->select('id', 'name', 'slug')->orderBy('name')->limit(200)->get()->map(fn($c) => (array) $c)->all();
    }

    public function save(): void
    {
        $data = $this->validate([
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
                'name' => $data['name'],
                'status' => 'active',
                'stacking_policy' => 'stack',
                'priority' => 100,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            switch ($data['preset']) {
                case 'sitewide_percent':
                    return DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'percentage',
                        'value' => (float) ($data['value'] ?? 0),
                    ]));
                case 'sitewide_fixed':
                    return DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'fixed',
                        'value' => (float) ($data['value'] ?? 0),
                    ]));
                case 'category_percent':
                    $id = DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'percentage',
                        'value' => (float) ($data['value'] ?? 0),
                    ]));
                    DB::table('sh_discount_conditions')->insert([
                        'discount_id' => $id,
                        'type' => 'category',
                        'operator' => 'equals_to',
                        'value' => json_encode([(int) ($data['category_id'] ?? 0)]),
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
                        'value' => (string) ((float) ($data['threshold'] ?? 0)),
                        'position' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    return $id;
                case 'first_order_fixed':
                    return DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'fixed',
                        'value' => (float) ($data['value'] ?? 0),
                        'first_order_only' => true,
                    ]));
                case 'bogo':
                    return DB::table('sh_discounts')->insertGetId(array_merge($base, [
                        'type' => 'bogo',
                        'value' => 0,
                        'metadata' => json_encode([
                            'buy_quantity' => (int) ($data['threshold'] ?? 1),
                            'get_quantity' => 1,
                            'discount_type' => 'percentage',
                            'discount_value' => (float) ($data['value'] ?? 100),
                        ]),
                    ]));
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

        $this->dispatch('notify', status: 'success', message: __('Preset created with ID: :id', ['id' => $discountId]));
        $this->reset('preset', 'name', 'value', 'category_id', 'threshold', 'currency');
    }

    #[Layout('layouts.templates.app')]
    public function render()
    {
        return view('livewire.admin.discounts.presets');
    }

    protected function applyPreferredLocale(): void
    {
        $preferred = request()->user()?->preferred_locale ?: request('locale');
        if (is_string($preferred) && $preferred !== '' && in_array($preferred, explode(',', (string) config('app.supported_locales', 'en')), true)) {
            AppFacade::setLocale($preferred);
        }
    }
}
