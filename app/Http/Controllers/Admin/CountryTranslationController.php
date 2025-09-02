<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class CountryTranslationController extends Controller
{
    public function update(Request $request, int $id, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_official' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($id, $locale, $data): void {
            $now = now();
            $exists = DB::table('country_translations')
                ->where('country_id', $id)
                ->where('locale', $locale)
                ->exists();

            $payload = [
                'name' => $data['name'],
                'name_official' => $data['name_official'] ?? null,
                'updated_at' => $now,
            ];

            if ($exists) {
                DB::table('country_translations')
                    ->where('country_id', $id)
                    ->where('locale', $locale)
                    ->update($payload);
            } else {
                DB::table('country_translations')->insert(array_merge($payload, [
                    'country_id' => $id,
                    'locale' => $locale,
                    'created_at' => $now,
                ]));
            }
        });

        Cache::forget("countries:translations:{$locale}");

        return redirect()->back()->with('success', $this->t('Country translation updated successfully'));
    }
}
