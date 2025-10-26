<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SeoData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SeoDataController
 *
 * HTTP controller handling SeoDataController related web requests, responses, and business logic with proper validation and error handling.
 */
final class SeoDataController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        $query = SeoData::query()
            ->with('seoable')
            ->orderBy('created_at', 'desc');

        // Filter by locale when an explicit locale has been provided.
        $locale = $request->input('locale');
        if (is_string($locale) && $locale !== '') {
            $query->where('locale', $locale);
        }

        // Filter by the polymorphic type when supplied by the caller.
        $typeFilter = $request->input('type');
        if (is_string($typeFilter) && $typeFilter !== '') {
            $query->where('seoable_type', $typeFilter);
        }

        // Search in title, description, and keywords using a grouped LIKE query.
        $search = $request->input('search');
        if (is_string($search) && $search !== '') {
            $like = '%' . $search . '%';
            $query->where(function (Builder $builder) use ($like): void {
                $builder
                    ->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('keywords', 'like', $like);
            });
        }

        // Ensure we only display records that have the essential SEO metadata present.
        $query->whereHas('seoable')
            ->whereNotNull('title')
            ->whereNotNull('description')
            ->whereNotNull('locale')
            ->whereNotNull('seoable_type')
            ->where('title', '<>', '')
            ->where('description', '<>', '')
            ->where('locale', '<>', '')
            ->where('seoable_type', '<>', '');

        $seoData = $query->paginate(20)->withQueryString();

        /** @var view-string $view */
        $view = 'seo-data.index';

        return view($view, compact('seoData'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(SeoData $seoData): View
    {
        $seoData->load('seoable');

        return view('seo-data.show', compact('seoData'));
    }

    /**
     * Handle byType functionality with proper error handling.
     */
    public function byType(string $type, Request $request): View
    {
        $query = SeoData::query()
            ->with('seoable')
            ->where('seoable_type', $type)
            ->orderBy('created_at', 'desc');

        // Apply locale filtering only when the caller provides the value.
        $locale = $request->input('locale');
        if (is_string($locale) && $locale !== '') {
            $query->where('locale', $locale);
        }

        // Apply the text search filter in a dedicated grouped condition.
        $search = $request->input('search');
        if (is_string($search) && $search !== '') {
            $like = '%' . $search . '%';
            $query->where(function (Builder $builder) use ($like): void {
                $builder
                    ->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('keywords', 'like', $like);
            });
        }

        // Guard against returning incomplete SEO payloads and enforce existing relations.
        $query->whereHas('seoable')
            ->whereNotNull('title')
            ->whereNotNull('description')
            ->whereNotNull('locale')
            ->where('title', '<>', '')
            ->where('description', '<>', '')
            ->where('locale', '<>', '');

        $seoData = $query->paginate(20)->withQueryString();

        /** @var view-string $view */
        $view = 'seo-data.by-type';

        return view($view, compact('seoData', 'type'));
    }

    /**
     * Handle analytics functionality with proper error handling.
     */
    public function analytics(): View
    {
        $stats = ['total' => SeoData::count(), 'by_locale' => SeoData::selectRaw('locale, COUNT(*) as count')->groupBy('locale')->pluck('count', 'locale'), 'by_type' => SeoData::selectRaw('seoable_type, COUNT(*) as count')->groupBy('seoable_type')->pluck('count', 'seoable_type'), 'avg_score' => SeoData::avg('seo_score') ?? 0, 'complete_seo' => SeoData::whereNotNull('title')->whereNotNull('description')->whereNotNull('keywords')->count(), 'needs_optimization' => SeoData::where(function ($q) {
            $q->whereNull('title')->orWhereNull('description')->orWhereNull('keywords');
        })->count()];

        return view('seo-data.analytics', compact('stats'));
    }
}
