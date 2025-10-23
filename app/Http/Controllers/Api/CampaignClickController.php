<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignClickRequest;
use App\Http\Requests\UpdateCampaignClickRequest;
use App\Http\Resources\CampaignClickResource;
use App\Models\CampaignClick;
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CampaignClickController
 *
 * HTTP controller handling CampaignClickController related web requests, responses, and business logic with proper validation and error handling.
 */
final class CampaignClickController extends Controller
{
    #[OA\Get(
        path: '/campaign-clicks',
        summary: 'List campaign clicks',
        description: 'Return a paginated collection of tracked campaign clicks. Unauthenticated requests receive public data, while authenticated requests are scoped to the current customer.',
        tags: ['Campaign Clicks'],
        parameters: [
            new OA\QueryParameter(name: 'page', description: 'Page number to retrieve.', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (1-100).', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
            new OA\QueryParameter(name: 'sort', description: 'Sort definition, e.g. `-clicked_at` or `conversion_value`.', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'campaign_id', description: 'Filter clicks by campaign identifier.', in: 'query', schema: new OA\Schema(type: 'integer', format: 'int64')),
            new OA\QueryParameter(name: 'click_type', description: 'Filter by click type (cta, banner, link, button, image).', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'device_type', description: 'Filter by originating device type.', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'is_converted', description: 'Limit to converted (true) or unconverted (false) clicks.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\QueryParameter(name: 'country', description: 'Filter by ISO country code.', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'utm_source', description: 'Filter by UTM source.', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'date_from', description: 'Filter clicks recorded after this ISO-8601 timestamp.', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\QueryParameter(name: 'date_to', description: 'Filter clicks recorded before this ISO-8601 timestamp.', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\QueryParameter(name: 'search', description: 'Full-text search across UTM and device details.', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated campaign click collection.',
                content: new OA\JsonContent(ref: '#/components/schemas/CampaignClickCollectionResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated access attempt.',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid filter or pagination parameters.',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationProblemDetails')
            ),
        ]
    )]
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $definition = new ListQueryDefinition(
            filters: [
                'campaign_id'  => ['type' => 'int', 'column' => 'campaign_clicks.campaign_id'],
                'click_type'   => ['type' => 'string', 'column' => 'campaign_clicks.click_type'],
                'device_type'  => ['type' => 'string', 'column' => 'campaign_clicks.device_type'],
                'is_converted' => ['type' => 'bool', 'column' => 'campaign_clicks.is_converted'],
                'country'      => ['type' => 'string', 'column' => 'campaign_clicks.country'],
                'utm_source'   => ['type' => 'string', 'column' => 'campaign_clicks.utm_source'],
                'date_from'    => ['type' => 'datetime', 'column' => 'campaign_clicks.clicked_at', 'operator' => '>='],
                'date_to'      => ['type' => 'datetime', 'column' => 'campaign_clicks.clicked_at', 'operator' => '<='],
                'search'       => [
                    'type'     => 'string',
                    'callback' => static function (Builder $builder, string $value): void {
                        $builder->where(function (Builder $query) use ($value): void {
                            $query->where('utm_source', 'like', "%{$value}%")
                                ->orWhere('click_type', 'like', "%{$value}%")
                                ->orWhere('device_type', 'like', "%{$value}%")
                                ->orWhere('country', 'like', "%{$value}%");
                        });
                    },
                ],
            ],
            sortable: [
                'clicked_at'       => ['column' => 'campaign_clicks.clicked_at', 'default_direction' => 'desc'],
                'conversion_value' => ['column' => 'campaign_clicks.conversion_value'],
            ],
            defaultSort: 'clicked_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            maxPerPage: 100,
        );

        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $query = CampaignClick::with(['campaign', 'customer']);
        $listQuery->applyFilters($query);

        // For authenticated users, show only their clicks
        if (Auth::check()) {
            $query->where('customer_id', Auth::id());
        }

        $listQuery->applySorts($query);

        if (! $listQuery->hasSort('clicked_at')) {
            $query->orderByDesc('campaign_clicks.clicked_at');
        }

        $clicks = $query->paginate($listQuery->perPage(), ['*'], 'page', $listQuery->page());

        return response()->json((new CampaignClickCollection($clicks))->withListQuery($listQuery));
    }

    #[OA\Post(
        path: '/campaign-clicks',
        summary: 'Record a campaign click',
        description: 'Create a campaign click entry from storefront or partner telemetry.',
        tags: ['Campaign Clicks'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CampaignClickCreate')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Campaign click recorded.',
                content: new OA\JsonContent(ref: '#/components/schemas/CampaignClickResourceResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed.',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationProblemDetails')
            ),
            new OA\Response(
                response: 500,
                description: 'Unexpected error while recording the click.',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
        ]
    )]
    /**
     * Store a newly created resource in storage with validation.
     */
    public function store(StoreCampaignClickRequest $request): JsonResponse
    {
        $click = CampaignClick::create($request->validated());

        return response()->json(['data' => new CampaignClickResource($click), 'message' => __('campaign_clicks.created_successfully')], 201);
    }

    #[OA\Get(
        path: '/campaign-clicks/{id}',
        summary: 'View a campaign click',
        description: 'Retrieve a single campaign click record with related campaign and customer information.',
        tags: ['Campaign Clicks'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'Campaign click identifier.', in: 'path', required: true, schema: new OA\Schema(type: 'integer', format: 'int64')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Campaign click details.',
                content: new OA\JsonContent(ref: '#/components/schemas/CampaignClickResourceResponse')
            ),
            new OA\Response(
                response: 403,
                description: 'Authenticated user is not allowed to view this click.',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
            new OA\Response(
                response: 404,
                description: 'Click not found.',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
        ]
    )]
    /**
     * Display the specified resource with related data.
     */
    public function show(CampaignClick $campaignClick): JsonResponse
    {
        // Check if user can view this click
        if (Auth::check() && $campaignClick->customer_id !== Auth::id()) {
            return response()->json(['message' => __('campaign_clicks.unauthorized')], 403);
        }

        return response()->json(['data' => new CampaignClickResource($campaignClick->load(['campaign', 'customer', 'conversions']))]);
    }

    #[OA\Put(
        path: '/campaign-clicks/{id}',
        summary: 'Update a campaign click',
        description: 'Update a campaign click that belongs to the authenticated customer.',
        tags: ['Campaign Clicks'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'Campaign click identifier.', in: 'path', required: true, schema: new OA\Schema(type: 'integer', format: 'int64')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CampaignClickUpdate')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Campaign click updated.',
                content: new OA\JsonContent(ref: '#/components/schemas/CampaignClickResourceResponse')
            ),
            new OA\Response(
                response: 403,
                description: 'Authenticated user is not allowed to modify this click.',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed.',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationProblemDetails')
            ),
        ]
    )]
    /**
     * Update the specified resource in storage with validation.
     */
    public function update(UpdateCampaignClickRequest $request, CampaignClick $campaignClick): JsonResponse
    {
        // Check if user can update this click
        if (Auth::check() && $campaignClick->customer_id !== Auth::id()) {
            return response()->json(['message' => __('campaign_clicks.unauthorized')], 403);
        }
        $campaignClick->update($request->validated());

        return response()->json(['data' => new CampaignClickResource($campaignClick), 'message' => __('campaign_clicks.updated_successfully')]);
    }

    #[OA\Delete(
        path: '/campaign-clicks/{id}',
        summary: 'Delete a campaign click',
        description: 'Delete a campaign click that belongs to the authenticated customer.',
        tags: ['Campaign Clicks'],
        parameters: [
            new OA\Parameter(name: 'id', description: 'Campaign click identifier.', in: 'path', required: true, schema: new OA\Schema(type: 'integer', format: 'int64')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Campaign click deleted.'),
            new OA\Response(
                response: 403,
                description: 'Authenticated user is not allowed to delete this click.',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
        ]
    )]
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CampaignClick $campaignClick): JsonResponse
    {
        // Check if user can delete this click
        if (Auth::check() && $campaignClick->customer_id !== Auth::id()) {
            return response()->json(['message' => __('campaign_clicks.unauthorized')], 403);
        }
        $campaignClick->delete();

        return response()->json(['message' => __('campaign_clicks.deleted_successfully')], 204);
    }

    #[OA\Get(
        path: '/campaign-clicks/statistics',
        summary: 'Summarise campaign click statistics',
        description: 'Aggregate quick statistics for campaign clicks available to the current caller.',
        tags: ['Campaign Clicks'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Aggregate statistics.',
                content: new OA\JsonContent(ref: '#/components/schemas/CampaignClickStatistics')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated access attempt.',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
        ]
    )]
    /**
     * Handle statistics functionality with proper error handling.
     */
    public function statistics(): JsonResponse
    {
        $query = CampaignClick::query();
        // For authenticated users, show only their statistics
        if (Auth::check()) {
            $query->where('customer_id', Auth::id());
        }
        $totalClicks = $query->count();
        $convertedClicks = $query->where('is_converted', true)->count();
        $conversionRate = $totalClicks > 0 ? round($convertedClicks / $totalClicks * 100, 2) : 0;
        $totalConversionValue = $query->where('is_converted', true)->sum('conversion_value');
        $todayClicks = $query->whereDate('clicked_at', today())->count();
        $thisWeekClicks = $query->whereBetween('clicked_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        return response()->json(['total_clicks' => $totalClicks, 'converted_clicks' => $convertedClicks, 'conversion_rate' => $conversionRate, 'total_conversion_value' => $totalConversionValue, 'today_clicks' => $todayClicks, 'this_week_clicks' => $thisWeekClicks]);
    }

    #[OA\Get(
        path: '/campaign-clicks/analytics',
        summary: 'Get campaign click analytics',
        description: 'Return grouped analytics such as daily counts, device mix, and geographic summaries.',
        tags: ['Campaign Clicks'],
        parameters: [
            new OA\QueryParameter(name: 'days', description: 'Number of trailing days to include (default 30).', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Analytics payload.',
                content: new OA\JsonContent(ref: '#/components/schemas/CampaignClickAnalytics')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated access attempt.',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
        ]
    )]
    /**
     * Handle analytics functionality with proper error handling.
     */
    public function analytics(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $query = CampaignClick::query();
        // For authenticated users, show only their analytics
        if (Auth::check()) {
            $query->where('customer_id', Auth::id());
        }
        $query->where('clicked_at', '>=', now()->subDays($days));
        // Clicks over time
        $clicksOverTime = $query->select(DB::raw('DATE(clicked_at) as date'), DB::raw('COUNT(*) as count'))->groupBy('date')->orderBy('date')->get();
        // Device types
        $deviceTypes = $query->select('device_type', DB::raw('COUNT(*) as count'))->whereNotNull('device_type')->groupBy('device_type')->get();
        // Browsers
        $browsers = $query->select('browser', DB::raw('COUNT(*) as count'))->whereNotNull('browser')->groupBy('browser')->orderByDesc('count')->limit(10)->get();
        // Countries
        $countries = $query->select('country', DB::raw('COUNT(*) as count'))->whereNotNull('country')->groupBy('country')->orderByDesc('count')->limit(10)->get();
        // UTM sources
        $utmSources = $query->select('utm_source', DB::raw('COUNT(*) as count'))->whereNotNull('utm_source')->groupBy('utm_source')->orderByDesc('count')->limit(10)->get();

        return response()->json(['clicks_over_time' => $clicksOverTime, 'device_types' => $deviceTypes, 'browsers' => $browsers, 'countries' => $countries, 'utm_sources' => $utmSources]);
    }

    #[OA\Get(
        path: '/campaign-clicks/export',
        summary: 'Export campaign clicks',
        description: 'Export campaign click data as a streamed CSV file with the active filters applied.',
        tags: ['Campaign Clicks'],
        parameters: [
            new OA\QueryParameter(name: 'campaign_id', in: 'query', schema: new OA\Schema(type: 'integer', format: 'int64')),
            new OA\QueryParameter(name: 'click_type', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'date_from', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\QueryParameter(name: 'date_to', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\QueryParameter(name: 'device_type', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'country', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'utm_source', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'CSV export of campaign clicks.',
                content: new OA\MediaType(mediaType: 'text/csv')
            ),
        ]
    )]
    /**
     * Handle export functionality with proper error handling.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = CampaignClick::with(['campaign', 'customer']);
        // Apply same filters as index
        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }
        if ($request->has('click_type')) {
            $query->where('click_type', $request->click_type);
        }
        if ($request->has('date_from')) {
            $query->where('clicked_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('clicked_at', '<=', $request->date_to);
        }
        // For authenticated users, export only their clicks
        if (Auth::check()) {
            $query->where('customer_id', Auth::id());
        }
        $clicks = $query->orderBy('clicked_at', 'desc')->get();
        $format = $request->get('format', 'csv');
        $filename = 'campaign_clicks_' . now()->format('Y-m-d_H-i-s') . '.' . $format;
        if ($format === 'csv') {
            return $this->exportCsv($clicks, $filename);
        }

        return response()->json(['message' => __('campaign_clicks.unsupported_format')], 400);
    }

    /**
     * Handle exportCsv functionality with proper error handling.
     *
     * @param  mixed                                             $clicks
     * @return Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportCsv($clicks, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="' . $filename . '"'];

        return response()->stream(function () use ($clicks): void {
            $handle = fopen('php://output', 'w');
            // Add BOM for UTF-8
            fwrite($handle, '﻿');
            // CSV headers
            fputcsv($handle, ['ID', __('campaign_clicks.campaign'), __('campaign_clicks.customer'), __('campaign_clicks.click_type'), __('campaign_clicks.clicked_url'), __('campaign_clicks.clicked_at'), __('campaign_clicks.device_type'), __('campaign_clicks.browser'), __('campaign_clicks.country'), __('campaign_clicks.utm_source'), __('campaign_clicks.converted'), __('campaign_clicks.conversion_value')]);
            // Use LazyCollection with timeout to prevent long-running export operations
            $timeout = now()->addMinutes(10);
            // 10 minute timeout for campaign click exports
            LazyCollection::make($clicks)->takeUntilTimeout($timeout)->each(function ($click) use ($handle): void {
                fputcsv($handle, [$click->id, $click->campaign->name ?? '', $click->customer->name ?? __('campaign_clicks.guest'), $click->click_type_label, $click->clicked_url, $click->clicked_at->format('Y-m-d H:i:s'), $click->device_type_label, $click->browser_label, $click->country, $click->utm_source, $click->is_converted ? __('campaign_clicks.yes') : __('campaign_clicks.no'), $click->conversion_value]);
            });
            fclose($handle);
        }, 200, $headers);
}

    private function campaignClickListDefinition(): ListQueryDefinition
    {
        return ListQueryDefinition::make()
            ->defaultPerPage(15)
            ->maxPerPage(100)
            ->defaultSort('clicked_at', 'desc')
            ->allowedSorts([
                'clicked_at' => ['column' => 'clicked_at'],
                'created_at' => ['column' => 'created_at'],
                'conversion_value' => ['column' => 'conversion_value'],
            ])
            ->filters([
                'campaign_id' => ['type' => 'int', 'column' => 'campaign_id'],
                'click_type' => ['type' => 'string', 'column' => 'click_type'],
                'device_type' => ['type' => 'string', 'column' => 'device_type'],
                'is_converted' => ['type' => 'bool', 'column' => 'is_converted'],
                'country' => ['type' => 'string', 'column' => 'country'],
                'utm_source' => ['type' => 'string', 'column' => 'utm_source'],
                'date_from' => ['type' => 'date', 'column' => 'clicked_at', 'operator' => '>='],
                'date_to' => ['type' => 'date', 'column' => 'clicked_at', 'operator' => '<='],
            ]);
    }
}
