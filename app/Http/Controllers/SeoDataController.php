<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SeoData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stringable;

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
        // Normalise the incoming filter values so downstream logic stays predictable.
        $filters = $this->extractFilters($request->input('locale'), $request->input('type'), $request->input('search'));

        // Build the shared query instance with the sanitised filters applied.
        $seoData = $this->buildListingQuery($filters)
            ->paginate(20);

        // Persist the filter selections on the paginator to keep pagination links in sync.
        $this->appendFiltersToPaginator($seoData, $filters);

        /** @var view-string $view */
        $view = 'seo-data.index';

        return view($view, [
            'seoData' => $seoData,
            'filters' => $filters,
        ]);
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
        // Merge the route parameter into the filter payload so all downstream logic reuses the same pathway.
        $filters = $this->extractFilters($request->input('locale'), $type, $request->input('search'));

        // Abort with a not-found response when the provided type is not a valid morph target.
        if ($filters['type'] === null) {
            abort(404);
        }

        // Build the listing query with the fixed type filter applied and paginate the results.
        $seoData = $this->buildListingQuery($filters)
            ->paginate(20);

        // Keep the active filters visible when navigating across pagination links.
        $this->appendFiltersToPaginator($seoData, $filters);

        /** @var view-string $view */
        $view = 'seo-data.index';

        return view($view, [
            'seoData' => $seoData,
            'filters' => $filters,
        ]);
    }

    /**
     * Render the statistics dashboard to surface aggregated SEO metrics without analytics phrasing.
     */
    public function statistics(): View
    {
        // Build the high level statistics map so the view only needs to render presentation logic.
        $statistics = [
            'total'              => SeoData::count(),
            'by_locale'          => SeoData::selectRaw('locale, COUNT(*) as count')->groupBy('locale')->pluck('count', 'locale'),
            'by_type'            => SeoData::selectRaw('seoable_type, COUNT(*) as count')->groupBy('seoable_type')->pluck('count', 'seoable_type'),
            'avg_score'          => SeoData::avg('seo_score') ?? 0,
            'complete_seo'       => SeoData::whereNotNull('title')->whereNotNull('description')->whereNotNull('keywords')->count(),
            'needs_optimization' => SeoData::where(static function (Builder $query): void {
                // Keep the closure focused on filtering incomplete records for optimisation follow-up.
                $query
                    ->whereNull('title')
                    ->orWhereNull('description')
                    ->orWhereNull('keywords');
            })->count(),
        ];

        return view('seo-data.statistics', compact('statistics'));
    }

    /**
     * Build the base listing query with the provided filters applied in a consistent manner.
     *
     * @param  array{locale:?string,type:?string,search:?string} $filters
     * @return Builder<SeoData>
     */
    private function buildListingQuery(array $filters): Builder
    {
        // Start from the SeoData model and eager-load related morph targets to avoid N+1 queries.
        $query = SeoData::query()
            ->with('seoable')
            ->orderByDesc('created_at');

        // Apply optional filtering on the locale column when a valid locale code is present.
        if ($filters['locale'] !== null) {
            $query->where('locale', $filters['locale']);
        }

        // Apply optional filtering on the morph type column for class-based routing.
        if ($filters['type'] !== null) {
            $query->where('seoable_type', $filters['type']);
        }

        // Apply optional text search by inspecting title, description, and keywords concurrently.
        if ($filters['search'] !== null) {
            $like = '%' . $filters['search'] . '%';
            $query->where(function (Builder $builder) use ($like): void {
                $builder
                    ->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('keywords', 'like', $like);
            });
        }

        // Guard against records with missing essential metadata or detached relations.
        return $query
            ->whereHas('seoable')
            ->whereNotNull('title')
            ->whereNotNull('description')
            ->whereNotNull('locale')
            ->whereNotNull('seoable_type')
            ->where('title', '<>', '')
            ->where('description', '<>', '')
            ->where('locale', '<>', '')
            ->where('seoable_type', '<>', '');
    }

    /**
     * Extract and sanitise the raw filter values so listing queries can rely on predictable input.
     *
     * @return array{locale:?string,type:?string,search:?string}
     */
    private function extractFilters(mixed $locale, mixed $type, mixed $search): array
    {
        // Resolve the supported application locales from configuration for quick validation.
        $supportedLocalesConfig = config('app.supported_locales', 'lt,en');
        if (is_array($supportedLocalesConfig)) {
            $rawLocales = $supportedLocalesConfig;
        } elseif (is_string($supportedLocalesConfig)) {
            $rawLocales = explode(',', $supportedLocalesConfig);
        } else {
            $rawLocales = ['lt', 'en'];
        }

        $supportedLocales = array_map(
            static function ($value): string {
                if ($value instanceof Stringable) {
                    $value = (string) $value;
                } elseif (! is_string($value)) {
                    return '';
                }

                return strtolower(trim($value));
            },
            $rawLocales
        );

        // Ensure the locale filter is a recognised language code, otherwise fall back to null.
        $localeFilter = is_string($locale) ? Str::lower(trim($locale)) : null;
        if ($localeFilter === '' || ! in_array($localeFilter, $supportedLocales, true)) {
            $localeFilter = null;
        }

        // Ensure the type filter points to a real morph target (either directly or via morph map).
        $typeFilter = is_string($type) ? trim($type) : null;
        if ($typeFilter === '') {
            $typeFilter = null;
        } elseif ($typeFilter !== null && ! class_exists($typeFilter)) {
            $morphMap = Relation::morphMap();
            $typeFilter = Arr::get($morphMap, $typeFilter, $typeFilter);

            if (! is_string($typeFilter) || ! class_exists($typeFilter)) {
                $typeFilter = null;
            }
        }

        // Normalise the search input and clamp it to a reasonable length to avoid excessive LIKE scans.
        $searchFilter = is_string($search) ? trim($search) : null;
        if ($searchFilter === '') {
            $searchFilter = null;
        } elseif ($searchFilter !== null && mb_strlen($searchFilter) > 120) {
            $searchFilter = mb_substr($searchFilter, 0, 120);
        }

        return [
            'locale' => $localeFilter,
            'type'   => $typeFilter,
            'search' => $searchFilter,
        ];
    }

    /**
     * Attach the active filters to the paginator so pagination links preserve the UI selections.
     *
     * @param LengthAwarePaginator<int, SeoData>                $paginator
     * @param array{locale:?string,type:?string,search:?string} $filters
     */
    private function appendFiltersToPaginator(LengthAwarePaginator $paginator, array $filters): void
    {
        // Filter out empty values to avoid noisy query strings and append the rest to the paginator.
        $paginator->appends(array_filter($filters, static fn ($value): bool => $value !== null));
    }
}
