<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttributeValueTranslationController extends Controller
{
    public function update(Request $request, int $id, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:255'],
            'key' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($id, $locale, $data): void {
            $now = now();
            $exists = DB::table('sh_attribute_value_translations')
                ->where('attribute_value_id', $id)
                ->where('locale', $locale)
                ->exists();

            $payload = [
                'value' => $data['value'],
                'key' => $data['key'] ?? null,
                'updated_at' => $now,
            ];

            if ($exists) {
                DB::table('sh_attribute_value_translations')
                    ->where('attribute_value_id', $id)
                    ->where('locale', $locale)
                    ->update($payload);
            } else {
                DB::table('sh_attribute_value_translations')->insert(array_merge($payload, [
                    'attribute_value_id' => $id,
                    'locale' => $locale,
                    'created_at' => $now,
                ]));
            }
        });

        Cache::forget("sitemap:urls:{$locale}");
        return back()->with('status', __('Translation saved for :locale', ['locale' => strtoupper($locale)]));
    }
}
