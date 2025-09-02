<?php declare(strict_types=1);

namespace App\Livewire\Admin\Discount;

use Illuminate\Support\Facades\App as AppFacade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class Codes extends Component
{
    public int $discountId;

    public ?object $discount = null;

    public array $codes = [];

    #[Url]
    public ?string $prefix = '';

    public int $length = 10;

    public int $quantity = 100;

    public function mount(int $discountId): void
    {
        $this->discountId = $discountId;
        $this->applyPreferredLocale();

        $this->discount = DB::table('sh_discounts')->where('id', $discountId)->first();
        abort_unless($this->discount, 404);

        $this->loadRecentCodes();
    }

    public function loadRecentCodes(): void
    {
        $this->codes = DB::table('sh_discount_codes')
            ->where('discount_id', $this->discountId)
            ->orderByDesc('id')
            ->limit(100)
            ->pluck('code')
            ->all();
    }

    public function generate(): void
    {
        $this->validate([
            'prefix' => ['nullable', 'string', 'max:16'],
            'length' => ['required', 'integer', 'min:4', 'max:32'],
            'quantity' => ['required', 'integer', 'min:1', 'max:5000'],
        ]);

        $prefix = Str::upper((string) ($this->prefix ?? ''));
        $length = (int) $this->length;
        $quantity = (int) $this->quantity;

        $rows = [];
        for ($i = 0; $i < $quantity; $i++) {
            $randLen = max(1, $length - strlen($prefix));
            $code = $prefix . Str::upper(Str::random($randLen));
            $rows[] = [
                'discount_id' => $this->discountId,
                'code' => $code,
                'expires_at' => null,
                'max_uses' => null,
                'usage_count' => 0,
                'metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            try {
                DB::table('sh_discount_codes')->insert($chunk);
            } catch (\Throwable) {
                // Ignore duplicates and continue
            }
        }

        // Export CSV to storage/app/exports
        $filename = 'discount_codes_' . $this->discountId . '_' . now()->format('Ymd_Hi') . '.csv';
        $path = 'exports/' . $filename;
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, ['code', 'max_uses', 'expires_at']);
        $exportCodes = DB::table('sh_discount_codes')
            ->where('discount_id', $this->discountId)
            ->orderByDesc('id')
            ->limit($quantity)
            ->pluck('code');
        foreach ($exportCodes as $c) {
            fputcsv($stream, [$c, '', '']);
        }
        rewind($stream);
        Storage::put($path, stream_get_contents($stream));
        fclose($stream);

        $this->dispatch('notify',
            status: 'success',
            message: trans_choice('Codes generated|:count codes generated', $quantity, ['count' => $quantity]) . ' ' . __('CSV saved to storage/app/:path', ['path' => $path]));

        $this->loadRecentCodes();
    }

    public function latestCsvPath(): ?string
    {
        $latest = collect(Storage::files('exports'))
            ->filter(fn($p) => str_contains($p, 'discount_codes_' . $this->discountId . '_'))
            ->sortDesc()
            ->first();

        return $latest ?: null;
    }

    #[Layout('layouts.templates.app')]
    public function render()
    {
        return view('livewire.admin.discounts.codes');
    }

    protected function applyPreferredLocale(): void
    {
        $preferred = request()->user()?->preferred_locale ?: request('locale');
        if (is_string($preferred) && $preferred !== '' && in_array($preferred, explode(',', (string) config('app.supported_locales', 'en')), true)) {
            AppFacade::setLocale($preferred);
        }
    }
}
