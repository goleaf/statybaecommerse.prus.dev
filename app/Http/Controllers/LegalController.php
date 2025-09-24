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
                        $qq->where('title', 'like', "%{$query}%")
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
            'document' => $document,
            'translation' => $translation,
            'relatedDocuments' => $relatedDocuments,
            'otherDocuments' => $otherDocuments,
        ]);
    }

    public function download(string $key, string $format = 'pdf'): Response
    {
        return redirect()->route('legal.show', $key);
    }

    public function sitemap(): Response
    {
        $translations = LegalTranslation::query()
            ->select(['slug', 'title'])
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($translations as $t) {
            $loc = e((string) url('/legal/'.$t->slug));
            $xml .= "<url><loc>{$loc}</loc><changefreq>weekly</changefreq></url>";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function rss(): Response
    {
        $translations = LegalTranslation::query()
            ->latest('updated_at')
            ->take(20)
            ->get();

        $rss = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<rss version="2.0"><channel>'
            .'<title>Legal Documents</title>'
            .'<link>'.e((string) url('/legal')).'</link>'
            .'<description>Latest legal documents</description>';

        foreach ($translations as $t) {
            $title = e((string) $t->title);
            $link = e((string) url('/legal/'.$t->slug));
            $desc = e((string) str(strip_tags((string) $t->content))->limit(200));
            $rss .= "<item><title>{$title}</title><link>{$link}</link><description>{$desc}</description></item>";
        }

        $rss .= '</channel></rss>';

        return response($rss, 200, ['Content-Type' => 'application/rss+xml']);
    }
}
