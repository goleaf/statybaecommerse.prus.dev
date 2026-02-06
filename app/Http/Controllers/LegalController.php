<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Legal;
use App\Models\Translations\LegalTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

final class LegalController extends Controller
{
    public function index(): Response
    {
        $documents = Legal::query()
            ->with('translations')
            ->enabled()
            ->published()
            ->ordered()
            ->get();

        /** @var Collection<string, Collection<int, Legal>> $groupedDocuments */
        $groupedDocuments = $documents->groupBy('type');

        return response()->view('legal.index', [
            'groupedDocuments' => $groupedDocuments,
        ]);
    }

    public function search(Request $request): Response
    {
        $query = (string) $request->get('q', '');
        $type = (string) $request->get('type', '');

        $documents = Legal::query()
            ->with('translations')
            ->when($type !== '', fn ($q) => $q->byType($type))
            ->whereHas('translations', function ($q) use ($query) {
                if ($query !== '') {
                    $q->where(function ($qq) use ($query) {
                        $qq
                            ->where('title', 'like', "%{$query}%")
                            ->orWhere('content', 'like', "%{$query}%");
                    });
                }
            })
            ->enabled()
            ->published()
            ->ordered()
            ->get();

        $groupedDocuments = $documents->groupBy('type');

        // Reuse index view to render results
        return response()->view('legal.index', [
            'groupedDocuments' => $groupedDocuments,
        ]);
    }

    public function type(string $type): Response
    {
        $documents = Legal::query()
            ->with('translations')
            ->byType($type)
            ->enabled()
            ->published()
            ->ordered()
            ->get();

        $groupedDocuments = $documents->groupBy('type');

        return response()->view('legal.index', [
            'groupedDocuments' => $groupedDocuments,
        ]);
    }

    public function show(string $key): Response
    {
        $document = Legal::query()
            ->with('translations')
            ->byKey($key)
            ->first();

        if ($document === null) {
            abort(404);
        }

        $preferredLocales = ['lt', app()->getLocale(), (string) config('app.locale', 'en'), 'en'];
        $translation = null;
        foreach ($preferredLocales as $loc) {
            $translation = $document->translations->firstWhere('locale', $loc);
            if ($translation) {
                break;
            }
        }
        if ($translation === null) {
            $translation = new LegalTranslation(['title' => $document->key, 'content' => '']);
        }

        $relatedDocuments = Legal::query()
            ->with('translations')
            ->byType($document->type)
            ->where('key', '!=', $document->key)
            ->enabled()
            ->published()
            ->ordered()
            ->limit(6)
            ->get();

        $otherDocuments = Legal::query()
            ->with('translations')
            ->where('type', '!=', $document->type)
            ->enabled()
            ->published()
            ->ordered()
            ->limit(6)
            ->get();

        return response()->view('legal.show', [
            'document'         => $document,
            'translation'      => $translation,
            'relatedDocuments' => $relatedDocuments,
            'otherDocuments'   => $otherDocuments,
        ]);
    }

    public function download(string $key, string $format = 'pdf')
    {
        return redirect()->route('legal.show', $key);
    }

    public function sitemap(): Response
    {
        $translations = LegalTranslation::query()
            ->select(['slug', 'title'])
            ->get();

        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return response('', 200, ['Content-Type' => 'text/csv; charset=utf-8']);
        }

        fputcsv($handle, ['loc', 'changefreq']);
        foreach ($translations as $t) {
            fputcsv($handle, [(string) url('/legal/' . $t->slug), 'weekly']);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response(is_string($csv) ? $csv : '', 200, ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    public function rss(): Response
    {
        $translations = LegalTranslation::query()
            ->latest('updated_at')
            ->take(20)
            ->get();

        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return response('', 200, ['Content-Type' => 'text/csv; charset=utf-8']);
        }

        fputcsv($handle, ['title', 'link', 'description']);
        foreach ($translations as $t) {
            fputcsv($handle, [
                (string) $t->title,
                (string) url('/legal/' . $t->slug),
                (string) str(strip_tags((string) $t->content))->limit(200),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response(is_string($csv) ? $csv : '', 200, ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
