<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SeoData;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SeoDataController extends Controller
{
    public function index(Request $request): View
    {
        $query = SeoData::with('seoable')
            ->orderBy('created_at', 'desc');

        // Filter by locale
        if ($request->has('locale') && $request->locale) {
            $query->where('locale', $request->locale);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('seoable_type', $request->type);
        }

        // Search in title and description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%");
            });
        }

        $seoData = $query->paginate(20);

        return view('seo-data.index', compact('seoData'));
    }

    public function show(SeoData $seoData): View
    {
        $seoData->load('seoable');

        return view('seo-data.show', compact('seoData'));
    }

    public function byType(string $type, Request $request): View
    {
        $query = SeoData::with('seoable')
            ->where('seoable_type', $type)
            ->orderBy('created_at', 'desc');

        // Filter by locale
        if ($request->has('locale') && $request->locale) {
            $query->where('locale', $request->locale);
        }

        // Search in title and description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%");
            });
        }

        $seoData = $query->paginate(20);

        return view('seo-data.by-type', compact('seoData', 'type'));
    }

    public function analytics(): View
    {
        $stats = [
            'total' => SeoData::count(),
            'by_locale' => SeoData::selectRaw('locale, COUNT(*) as count')
                ->groupBy('locale')
                ->pluck('count', 'locale'),
            'by_type' => SeoData::selectRaw('seoable_type, COUNT(*) as count')
                ->groupBy('seoable_type')
                ->pluck('count', 'seoable_type'),
            'avg_score' => SeoData::avg('seo_score') ?? 0,
            'complete_seo' => SeoData::whereNotNull('title')
                ->whereNotNull('description')
                ->whereNotNull('keywords')
                ->count(),
            'needs_optimization' => SeoData::where(function ($q) {
                $q->whereNull('title')
                    ->orWhereNull('description')
                    ->orWhereNull('keywords');
            })->count(),
        ];

        return view('seo-data.analytics', compact('stats'));
    }
}
