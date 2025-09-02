<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductTranslationController extends Controller
{
    public function update(Request $request, int $id, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($id, $locale, $data): void {
            $now = now();
            $exists = DB::table('sh_product_translations')
                ->where('product_id', $id)
                ->where('locale', $locale)
                ->exists();

            $payload = [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'summary' => $data['summary'] ?? null,
                'description' => $data['description'] ?? null,
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
                'updated_at' => $now,
            ];

            if ($exists) {
                DB::table('sh_product_translations')
                    ->where('product_id', $id)
                    ->where('locale', $locale)
                    ->update($payload);
            } else {
                DB::table('sh_product_translations')->insert(array_merge($payload, [
                    'product_id' => $id,
                    'locale' => $locale,
                    'created_at' => $now,
                ]));
            }
        });

        Cache::forget("sitemap:urls:{$locale}");

        return back()->with('status', __('Translation saved for :locale', ['locale' => strtoupper($locale)]));
    }
}
