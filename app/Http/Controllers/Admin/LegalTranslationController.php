<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LegalTranslationController extends Controller
{
    public function update(Request $request, int $id, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($id, $locale, $data): void {
            $now = now();
            $exists = DB::table('sh_legal_translations')
                ->where('legal_id', $id)
                ->where('locale', $locale)
                ->exists();

            if ($exists) {
                DB::table('sh_legal_translations')
                    ->where('legal_id', $id)
                    ->where('locale', $locale)
                    ->update([
                        'title' => $data['title'],
                        'slug' => $data['slug'],
                        'content' => $data['content'] ?? null,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('sh_legal_translations')->insert([
                    'legal_id' => $id,
                    'locale' => $locale,
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'content' => $data['content'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        return back()->with('status', __('Translation saved for :locale', ['locale' => strtoupper($locale)]));
    }
}
