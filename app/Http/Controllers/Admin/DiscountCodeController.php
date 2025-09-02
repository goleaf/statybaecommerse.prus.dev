<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiscountCodeController extends Controller
{
    public function create(int $discountId): View
    {
        $this->applyPreferredLocale();

        $discount = DB::table('sh_discounts')->where('id', $discountId)->first();
        abort_unless($discount, 404);
        $codes = DB::table('sh_discount_codes')->where('discount_id', $discountId)->orderByDesc('id')->limit(100)->get();
        return view('livewire.admin.discounts.codes', compact('discount', 'codes'));
    }

    public function store(Request $request, int $discountId): RedirectResponse
    {
        $this->applyPreferredLocale();

        $request->validate([
            'prefix' => ['nullable', 'string', 'max:16'],
            'length' => ['required', 'integer', 'min:4', 'max:32'],
            'quantity' => ['required', 'integer', 'min:1', 'max:5000'],
        ]);

        $discount = DB::table('sh_discounts')->where('id', $discountId)->first();
        abort_unless($discount, 404);

        $prefix = strtoupper((string) $request->input('prefix', ''));
        $length = (int) $request->integer('length');
        $quantity = (int) $request->integer('quantity');

        $rows = [];
        for ($i = 0; $i < $quantity; $i++) {
            $randLen = max(1, $length - strlen($prefix));
            $code = $prefix . Str::upper(Str::random($randLen));
            $rows[] = [
                'discount_id' => $discountId,
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
            } catch (\Throwable $e) {
            }
        }

        $filename = 'discount_codes_' . $discountId . '_' . now()->format('Ymd_Hi') . '.csv';
        $path = 'exports/' . $filename;
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, ['code', 'max_uses', 'expires_at']);
        $exportCodes = DB::table('sh_discount_codes')->where('discount_id', $discountId)->orderByDesc('id')->limit($quantity)->pluck('code');
        foreach ($exportCodes as $c) {
            fputcsv($stream, [$c, '', '']);
        }
        rewind($stream);
        Storage::put($path, stream_get_contents($stream));
        fclose($stream);

        return redirect()
            ->route('admin.discounts.codes', ['discountId' => $discountId])
            ->with('status', trans_choice('Codes generated|:count codes generated', $quantity, ['count' => $quantity]) . ' ' . __('CSV saved to storage/app/:path', ['path' => $path]));
    }

    public function download(int $discountId): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->applyPreferredLocale();

        $latest = collect(Storage::files('exports'))
            ->filter(fn($p) => str_contains($p, 'discount_codes_' . $discountId . '_'))
            ->sortDesc()
            ->first();
        abort_unless($latest, 404);
        return Storage::download($latest);
    }

    protected function applyPreferredLocale(): void
    {
        $preferred = request()->user()?->preferred_locale ?: request('locale');
        if (is_string($preferred) && $preferred !== '' && in_array($preferred, explode(',', (string) config('app.supported_locales', 'en')), true)) {
            App::setLocale($preferred);
        }
    }
}
