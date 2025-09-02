<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryTranslationController extends Controller
{
    public function update(Request $request, string $id, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($id, $locale, $data): void {
            $id = (int) $id;
            $now = now();
            $exists = DB::table('sh_category_translations')
                ->where('category_id', $id)
                ->where('locale', $locale)
                ->exists();

            $payload = [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
                'updated_at' => $now,
            ];

            if ($exists) {
                DB::table('sh_category_translations')
                    ->where('category_id', $id)
                    ->where('locale', $locale)
                    ->update($payload);
            } else {
                DB::table('sh_category_translations')->insert(array_merge($payload, [
                    'category_id' => $id,
                    'locale' => $locale,
                    'created_at' => $now,
                ]));
            }
        });

        // Invalidate caches (sitemap, category trees/navigation) for this locale
        Cache::forget("sitemap:urls:{$locale}");
        Cache::forget("categories:roots:{$locale}");
        Cache::forget("categories:tree:{$locale}");
        Cache::forget("nav:categories:roots:{$locale}");

        return back()->with('status', __('Translation saved for :locale', ['locale' => strtoupper($locale)]));
    }
}
