<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CollectionTranslationController extends Controller
{
    public function update(Request $request, string $id, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($id, $locale, $data): void {
            $id = (int) $id;
            $now = now();
            $exists = DB::table('collection_translations')
                ->where('collection_id', $id)
                ->where('locale', $locale)
                ->exists();

            $payload = [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'updated_at' => $now,
            ];

            if ($exists) {
                DB::table('collection_translations')
                    ->where('collection_id', $id)
                    ->where('locale', $locale)
                    ->update($payload);
            } else {
                DB::table('collection_translations')->insert(array_merge($payload, [
                    'collection_id' => $id,
                    'locale' => $locale,
                    'created_at' => $now,
                ]));
            }
        });

        Cache::forget("sitemap:urls:{$locale}");

        return back()->with('status', __('Translation saved for :locale', ['locale' => strtoupper($locale)]));
    }
}
