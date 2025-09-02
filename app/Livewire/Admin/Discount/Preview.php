<?php declare(strict_types=1);

namespace App\Livewire\Admin\Discount;

use App\Services\Discounts\DiscountEngine;
use Illuminate\Support\Facades\App as AppFacade;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class Preview extends Component
{
    public int $discountId;

    public ?object $discount = null;

    public array $currencies = [];

    public array $zones = [];

    #[Url]
    public ?string $currency_code = null;

    #[Url]
    public ?int $zone_id = null;

    public float $subtotal = 0.0;

    public ?string $code = null;

    public ?string $items = null;  // CSV product_id:qty:unit_price

    public array $result = [];

    public function mount(int $discountId): void
    {
        $this->discountId = $discountId;

        $this->discount = DB::table('sh_discounts')->where('id', $discountId)->first();
        abort_unless($this->discount, 404);

        $this->currencies = DB::table('sh_currencies')->where('is_enabled', 1)->pluck('code')->all();
        $this->zones = DB::table('sh_zones')->where('is_enabled', 1)->pluck('name', 'id')->all();

        $this->applyPreferredLocale();
    }

    public function compute(DiscountEngine $engine): void
    {
        $this->validate([
            'currency_code' => ['required', 'string', 'size:3'],
            'zone_id' => ['nullable', 'integer'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'code' => ['nullable', 'string', 'max:64'],
            'items' => ['nullable', 'string'],
        ]);

        $items = [];
        if (!empty($this->items)) {
            foreach (explode(',', $this->items) as $row) {
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

        $this->result = (array) $engine->evaluate([
            'zone_id' => $this->zone_id ?: null,
            'currency_code' => strtoupper((string) $this->currency_code),
            'channel_id' => null,
            'user_id' => auth()->id(),
            'now' => now(),
            'code' => $this->code ?: null,
            'cart' => [
                'subtotal' => (float) $this->subtotal,
                'items' => $items,
            ],
        ]);

        $this->dispatch('notify', status: 'success', message: __('Preview computed'));
    }

    #[Layout('layouts.templates.app')]
    public function render()
    {
        return view('livewire.admin.discounts.preview');
    }

    protected function applyPreferredLocale(): void
    {
        $preferred = request()->user()?->preferred_locale ?: request('locale');
        if (is_string($preferred) && $preferred !== '' && in_array($preferred, explode(',', (string) config('app.supported_locales', 'en')), true)) {
            AppFacade::setLocale($preferred);
        }
    }
}
