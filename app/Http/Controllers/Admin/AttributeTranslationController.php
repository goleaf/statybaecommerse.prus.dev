<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttributeTranslationController extends Controller
{
    public function update(Request $request, int $id, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($id, $locale, $data): void {
            $now = now();
            $exists = DB::table('sh_attribute_translations')
                ->where('attribute_id', $id)
                ->where('locale', $locale)
                ->exists();

            $payload = [
                'name' => $data['name'],
                'updated_at' => $now,
            ];

            if ($exists) {
                DB::table('sh_attribute_translations')
                    ->where('attribute_id', $id)
                    ->where('locale', $locale)
                    ->update($payload);
            } else {
                DB::table('sh_attribute_translations')->insert(array_merge($payload, [
                    'attribute_id' => $id,
                    'locale' => $locale,
                    'created_at' => $now,
                ]));
            }
        });

        Cache::forget("sitemap:urls:{$locale}");

        return back()->with('status', __('Translation saved for :locale', ['locale' => strtoupper($locale)]));
    }
}
