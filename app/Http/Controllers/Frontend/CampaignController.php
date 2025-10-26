<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignConversion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use OpenApi\Attributes as OA;

/**
 * CampaignController
 *
 * HTTP controller handling CampaignController related web requests, responses, and business logic with proper validation and error handling.
 */
#[OA\Tag(name: 'Campaigns', description: 'Campaign engagement tracking and analytics endpoints.')]
final class CampaignController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        $campaigns = Campaign::query()->active()->byPriority()->with(['targetCategories', 'targetProducts', 'channel'])->when($request->filled('type'), function ($query) use ($request) {
            return $query->where('type', $request->get('type'));
        })->when($request->filled('category'), function ($query) use ($request) {
            return $query->whereHas('targetCategories', function ($q) use ($request): void {
                $q->where('slug', $request->get('category'));
            });
        })->when($request->filled('search'), function ($query) use ($request) {
            return $query->where('name', 'like', '%' . $request->get('search') . '%');
        })->paginate(12);

        return view('campaigns.index', compact('campaigns'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Campaign $campaign): View
    {
        // Record view for analytics
        $campaign->recordView(session()->getId(), request()->ip(), request()->userAgent(), request()->header('referer'), auth()->id());
        $campaign->load(['targetCategories', 'targetProducts', 'targetCustomerGroups', 'channel', 'discounts']);
        // Get related campaigns
        $relatedCampaigns = Campaign::query()->active()->where('id', '!=', $campaign->id)->whereHas('targetCategories', function ($query) use ($campaign): void {
            $query->whereIn('categories.id', $campaign->targetCategories->pluck('id'));
        })->limit(4)->get();

        return view('campaigns.show', compact('campaign', 'relatedCampaigns'));
    }

    /**
     * Handle click functionality with proper error handling.
     */
    #[OA\Post(
        path: '/campaigns/{campaign}/click',
        summary: 'Record a campaign click event.',
        tags: ['Campaigns'],
        parameters: [
            new OA\PathParameter(
                name: 'campaign',
                description: 'Campaign identifier or slug.',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        requestBody: new OA\RequestBody(
            description: 'Optional click metadata.',
            required: false,
            content: new OA\JsonContent(ref: '#/components/schemas/CampaignInteractionRequest'),
        ),
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/CampaignAcknowledgement'),
        ]
    )]
    public function click(Request $request, Campaign $campaign): JsonResponse
    {
        $clickType = $request->get('type', 'cta');
        $clickedUrl = $request->get('url');
        $campaign->recordClick($clickType, $clickedUrl, session()->getId(), request()->ip(), request()->userAgent(), auth()->id());

        return response()->json(['success' => true, 'message' => __('campaigns.messages.click_recorded')]);
    }

    /**
     * Handle conversion functionality with proper error handling.
     */
    #[OA\Post(
        path: '/campaigns/{campaign}/conversion',
        summary: 'Record a campaign conversion event.',
        tags: ['Campaigns'],
        parameters: [
            new OA\PathParameter(
                name: 'campaign',
                description: 'Campaign identifier or slug.',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        requestBody: new OA\RequestBody(
            description: 'Conversion payload.',
            required: false,
            content: new OA\JsonContent(ref: '#/components/schemas/CampaignConversionRequest'),
        ),
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/CampaignAcknowledgement'),
        ]
    )]
    public function conversion(Request $request, Campaign $campaign): JsonResponse
    {
        $conversionType = $request->get('type', 'purchase');
        $conversionValue = (float) $request->get('value', 0);
        $orderId = $request->get('order_id');
        $conversionData = $request->get('data', []);
        $campaign->recordConversion($conversionType, $conversionValue, $orderId, auth()->id(), session()->getId(), $conversionData);

        return response()->json(['success' => true, 'message' => __('campaigns.messages.conversion_recorded')]);
    }

    /**
     * Handle featured functionality with proper error handling.
     */
    public function featured(): View
    {
        $campaigns = Campaign::query()->featured()->active()->byPriority()->with(['targetCategories', 'channel'])->limit(6)->get();

        return view('campaigns.featured', compact('campaigns'));
    }

    /**
     * Handle byType functionality with proper error handling.
     */
    public function byType(Request $request, string $type): View
    {
        $campaigns = Campaign::query()->active()->where('type', $type)->byPriority()->with(['targetCategories', 'targetProducts', 'channel'])->paginate(12);

        return view('campaigns.by-type', compact('campaigns', 'type'));
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(Request $request): View
    {
        $query = $request->get('q');
        $campaigns = Campaign::query()->active()->when($query, function ($q) use ($query) {
            return $q->where('name', 'like', '%' . $query . '%')->orWhere('description', 'like', '%' . $query . '%');
        })->byPriority()->with(['targetCategories', 'channel'])->paginate(12);

        return view('campaigns.search', compact('campaigns', 'query'));
    }

    /**
     * Handle getCampaignStatistics functionality with proper error handling.
     */
    #[OA\Get(
        path: '/campaigns/api/statistics',
        summary: 'Retrieve aggregated campaign statistics.',
        tags: ['Campaigns'],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/CampaignStatistics'),
        ]
    )]
    public function getCampaignStatistics(): JsonResponse
    {
        $statistics = ['total_campaigns' => Campaign::count(), 'active_campaigns' => Campaign::active()->count(), 'scheduled_campaigns' => Campaign::scheduled()->count(), 'completed_campaigns' => Campaign::where('status', 'completed')->count(), 'total_views' => Campaign::sum('total_views'), 'total_clicks' => Campaign::sum('total_clicks'), 'total_conversions' => Campaign::sum('total_conversions'), 'total_revenue' => Campaign::sum('total_revenue'), 'average_conversion_rate' => Campaign::where('total_views', '>', 0)->avg('conversion_rate') ?? 0, 'average_click_through_rate' => Campaign::where('total_views', '>', 0)->avg(DB::raw('(total_clicks / total_views) * 100')) ?? 0, 'average_roi' => Campaign::where('budget', '>', 0)->avg(DB::raw('((total_revenue - budget) / budget) * 100')) ?? 0];

        return response()->json(['success' => true, 'data' => $statistics]);
    }

    /**
     * Handle getCampaignTypes functionality with proper error handling.
     */
    #[OA\Get(
        path: '/campaigns/api/types',
        summary: 'List available campaign types and their usage counts.',
        tags: ['Campaigns'],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/CampaignTypes'),
        ]
    )]
    public function getCampaignTypes(): JsonResponse
    {
        $campaigns = Campaign::all();
        $types = [];
        foreach ($campaigns as $campaign) {
            $type = $campaign->type ?? 'unknown';
            $types[$type] = ($types[$type] ?? 0) + 1;
        }
        $formattedTypes = [];
        foreach ($types as $type => $count) {
            $formattedTypes[] = ['type' => $type, 'label' => __('campaigns.types.' . $type), 'count' => $count, 'icon' => match ($type) {
                'email'                 => 'heroicon-o-envelope',
                'sms'                   => 'heroicon-o-device-phone-mobile',
                'push'                  => 'heroicon-o-bell',
                'banner'                => 'heroicon-o-photo',
                'popup'                 => 'heroicon-o-window',
                'social'                => 'heroicon-o-share',
                default                 => 'heroicon-o-megaphone',
            }, 'color' => match ($type) {
                'email'  => 'blue',
                'sms'    => 'green',
                'push'   => 'yellow',
                'banner' => 'purple',
                'popup'  => 'pink',
                'social' => 'red',
                default  => 'gray',
            }];
        }

        return response()->json(['success' => true, 'data' => $formattedTypes]);
    }

    /**
     * Handle getCampaignPerformance functionality with proper error handling.
     */
    #[OA\Get(
        path: '/campaigns/api/performance',
        summary: 'Summarize campaign performance groupings.',
        tags: ['Campaigns'],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/CampaignPerformance'),
        ]
    )]
    public function getCampaignPerformance(): JsonResponse
    {
        $performance = ['high_performing' => Campaign::where('conversion_rate', '>', 5)->count(), 'medium_performing' => Campaign::whereBetween('conversion_rate', [2, 5])->count(), 'low_performing' => Campaign::where('conversion_rate', '<', 2)->count(), 'needs_attention' => Campaign::where(function ($query): void {
            $query->where('conversion_rate', '<', 2)->orWhere('total_views', '>', 0)->whereRaw('(total_clicks / total_views) < 0.01');
        })->count()];

        return response()->json(['success' => true, 'data' => $performance]);
    }

    /**
     * Handle getCampaignAnalytics functionality with proper error handling.
     */
    #[OA\Get(
        path: '/campaigns/api/analytics',
        summary: 'Retrieve campaign analytics for the requested period.',
        tags: ['Campaigns'],
        parameters: [
            new OA\QueryParameter(
                name: 'period',
                description: 'Rolling window (in days) to evaluate. Defaults to 30.',
                required: false,
                schema: new OA\Schema(type: 'string', example: '30'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/CampaignAnalytics'),
        ]
    )]
    public function getCampaignAnalytics(Request $request): JsonResponse
    {
        // Normalise the requested window and clamp extremes to keep the query predictable.
        $periodPayload = $this->normalisePeriod($request->input('period', 30));
        $period = $periodPayload['days'];

        $now = now();
        $startDate = $now->copy()->subDays($period);

        // Load campaign activity without global scopes so archived/scheduled records contribute to the audit.
        $campaigns = Campaign::query()
            ->withoutGlobalScopes()
            // Ensure we only analyse campaigns that existed within the requested window.
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $now)
            ->get([
                'id',
                'name',
                'metadata',
                'total_views',
                'total_clicks',
                'total_conversions',
                'total_revenue',
                'conversion_rate',
                'budget_limit',
                'starts_at',
                'ends_at',
                'status',
                'created_at',
            ]);

        // Collect conversion level telemetry to power attribution, ROI, and journey metrics.
        $conversions = CampaignConversion::query()
            // Restrict attribution analytics to conversions that actually happened within the window.
            ->where('converted_at', '>=', $startDate)
            ->where('converted_at', '<=', $now)
            ->get([
                'id',
                'campaign_id',
                'conversion_value',
                'conversion_rate',
                'attribution_model',
                'funnel_step',
                'conversion_path',
                'touchpoints',
                'conversion_data',
                'roi',
                'roas',
                'assisted_conversions',
                'assisted_conversion_value',
                'is_verified',
                'is_attributed',
                'cost_per_conversion',
                'page_views',
                'time_on_site',
            ]);

        // Summarise campaign engagement KPIs.
        $engagementCampaigns = $campaigns->map(static function (Campaign $campaign): array {
            $views = (int) ($campaign->total_views ?? data_get($campaign->metadata, 'total_views', 0));
            $clicks = (int) ($campaign->total_clicks ?? data_get($campaign->metadata, 'total_clicks', 0));
            $ctr = method_exists($campaign, 'getClickThroughRate')
                ? (float) $campaign->getClickThroughRate()
                : ($views > 0 ? round($clicks / $views * 100, 2) : 0.0);

            return [
                'id'                 => $campaign->getKey(),
                'name'               => $campaign->name,
                'views'              => $views,
                'clicks'             => $clicks,
                'click_through_rate' => round($ctr, 2),
                'status'             => $campaign->status,
            ];
        });

        $totalViews = (int) $engagementCampaigns->sum('views');
        $totalClicks = (int) $engagementCampaigns->sum('clicks');
        $averageCtr = $engagementCampaigns
            ->filter(static fn (array $campaign): bool => $campaign['views'] > 0)
            ->avg(static fn (array $campaign): float => $campaign['click_through_rate']) ?? 0.0;

        $topEngagementCampaigns = $engagementCampaigns
            ->sortByDesc('clicks')
            ->take(5)
            ->values()
            ->all();

        // Aggregate campaign type breakdowns while honouring accessor-backed payloads.
        $typeSegments = $campaigns->map(static function (Campaign $campaign): array {
            $breakdown = $campaign->getAttribute('type_breakdown') ?? data_get($campaign->metadata, 'type_breakdown');

            if (is_array($breakdown) && ! empty($breakdown)) {
                if (array_is_list($breakdown)) {
                    return collect($breakdown)->mapWithKeys(static function ($item): array {
                        if (is_array($item)) {
                            $key = (string) ($item['type'] ?? $item['key'] ?? $item['name'] ?? 'unknown');
                            $count = (float) ($item['count'] ?? $item['value'] ?? 0.0);

                            return [$key => $count > 0 ? $count : 0.0];
                        }

                        $key = (string) $item;

                        return [$key => 1.0];
                    })->all();
                }

                return collect($breakdown)->map(static fn ($value): float => (float) $value)->all();
            }

            $typeKey = (string) ($campaign->type ?? 'unknown');

            return [$typeKey => 1.0];
        });

        $typeTotals = [];

        foreach ($typeSegments as $segment) {
            foreach ($segment as $typeKey => $count) {
                $typeTotals[$typeKey] = ($typeTotals[$typeKey] ?? 0.0) + (float) $count;
            }
        }

        $representatives = [];

        foreach ($campaigns as $campaign) {
            $typeKey = (string) ($campaign->type ?? 'unknown');

            if (! array_key_exists($typeKey, $representatives)) {
                $representatives[$typeKey] = $campaign;
            }
        }

        $typeBreakdown = collect($typeTotals)
            ->map(static function (float $count, string $typeKey) use ($representatives, $campaigns): array {
                $representative = $representatives[$typeKey] ?? null;
                $label = $representative?->type_label ?? Str::of($typeKey)->headline()->toString();
                $icon = $representative?->type_icon ?? null;
                $color = $representative?->type_color ?? null;
                $totalCampaigns = max($campaigns->count(), 1);

                return [
                    'type'       => $typeKey,
                    'label'      => $label,
                    'count'      => (int) round($count),
                    'percentage' => round($count / $totalCampaigns * 100, 2),
                    'icon'       => $icon,
                    'color'      => $color,
                ];
            })
            ->values()
            ->all();

        // Derive conversion-focused analytics.
        $totalConversions = $conversions->count();
        $averageConversionRate = ($conversions->avg(static fn (CampaignConversion $conversion): float => (float) ($conversion->conversion_rate ?? 0)) ?? 0.0) * 100;
        $verifiedConversions = $conversions->filter(static fn (CampaignConversion $conversion): bool => (bool) $conversion->is_verified)->count();
        $attributedConversions = $conversions->filter(static fn (CampaignConversion $conversion): bool => (bool) $conversion->is_attributed)->count();
        $assistedConversions = (int) $conversions->sum(static fn (CampaignConversion $conversion): int => (int) ($conversion->assisted_conversions ?? 0));
        $assistedConversionValue = (float) $conversions->sum(static fn (CampaignConversion $conversion): float => (float) ($conversion->assisted_conversion_value ?? 0));
        $attributionBreakdown = $conversions
            ->groupBy(static fn (CampaignConversion $conversion): string => (string) ($conversion->attribution_model ?? 'unspecified'))
            ->map(static function ($group) use ($totalConversions) {
                $count = $group->count();
                $avgRate = ($group->avg(static fn (CampaignConversion $conversion): float => (float) ($conversion->conversion_rate ?? 0)) ?? 0.0) * 100;

                return [
                    'model'                   => (string) ($group->first()->attribution_model ?? 'unspecified'),
                    'conversions'             => $count,
                    'percentage'              => $totalConversions > 0 ? round($count / $totalConversions * 100, 2) : 0.0,
                    'average_conversion_rate' => round($avgRate, 2),
                    'average_value'           => round((float) $group->avg(static fn (CampaignConversion $conversion): float => (float) ($conversion->conversion_value ?? 0)), 2),
                ];
            })
            ->values()
            ->all();

        // Compute ROI and budget efficiency metrics.
        $totalRevenue = (float) $campaigns->sum(static fn (Campaign $campaign): float => (float) ($campaign->getOriginal('total_revenue') ?? data_get($campaign->metadata, 'total_revenue', 0)));
        $totalBudget = (float) $campaigns->sum(static fn (Campaign $campaign): float => (float) (data_get($campaign->metadata, 'budget', $campaign->budget_limit ?? 0)));
        $roiPercentage = $totalBudget > 0 ? round(($totalRevenue - $totalBudget) / $totalBudget * 100, 2) : 0.0;
        $roas = $totalBudget > 0 ? round($totalRevenue / $totalBudget, 2) : 0.0;
        $averageRoi = (float) ($conversions->avg(static fn (CampaignConversion $conversion): float => (float) ($conversion->roi ?? 0)) ?? 0.0);
        $averageRoas = (float) ($conversions->avg(static fn (CampaignConversion $conversion): float => (float) ($conversion->roas ?? 0)) ?? 0.0);
        $averageCostPerConversion = (float) ($conversions->avg(static fn (CampaignConversion $conversion): float => (float) ($conversion->cost_per_conversion ?? 0)) ?? 0.0);

        // Translate conversion telemetry into journey insights.
        $funnelBreakdown = $conversions
            ->groupBy(static fn (CampaignConversion $conversion): string => (string) ($conversion->funnel_step ?? 'unknown'))
            ->map(static function ($group) use ($totalConversions) {
                $count = $group->count();

                return [
                    'step'        => (string) ($group->first()->funnel_step ?? 'unknown'),
                    'conversions' => $count,
                    'percentage'  => $totalConversions > 0 ? round($count / $totalConversions * 100, 2) : 0.0,
                ];
            })
            ->values()
            ->all();

        $averageTouchpoints = $conversions->avg(static function (CampaignConversion $conversion): ?float {
            $pathTouchpoints = data_get($conversion->conversion_path, 'touchpoints');

            if (is_numeric($pathTouchpoints)) {
                return (float) $pathTouchpoints;
            }

            $touchpointArray = $conversion->touchpoints;

            if (is_array($touchpointArray)) {
                return (float) count(array_filter($touchpointArray));
            }

            return null;
        }) ?? 0.0;

        $averageTimeOnSite = (float) ($conversions->avg(static fn (CampaignConversion $conversion): float => (float) ($conversion->time_on_site ?? 0)) ?? 0.0);
        $averagePageViews = (float) ($conversions->avg(static fn (CampaignConversion $conversion): float => (float) ($conversion->page_views ?? 0)) ?? 0.0);

        // Surface simple multi-variant comparisons by mining conversion payload metadata.
        $conversionsWithCampaign = $conversions
            ->filter(static fn (CampaignConversion $conversion): bool => $conversion->campaign_id !== null);

        $variantPerformance = $conversionsWithCampaign
            ->groupBy(static fn (CampaignConversion $conversion): int => (int) $conversion->campaign_id)
            ->flatMap(static function ($campaignConversions, int $campaignId) use ($campaigns) {
                $campaignName = optional($campaigns->firstWhere('id', $campaignId))->name ?? sprintf('Campaign #%d', $campaignId);

                return $campaignConversions
                    ->groupBy(static fn (CampaignConversion $conversion): string => (string) data_get($conversion->conversion_data, 'campaign_name', 'Standard Variant'))
                    ->map(static function ($variantGroup, string $variantName) use ($campaignId, $campaignName) {
                        $conversionValue = (float) $variantGroup->sum(static fn (CampaignConversion $conversion): float => (float) ($conversion->conversion_value ?? 0));
                        $roiAverage = (float) ($variantGroup->avg(static fn (CampaignConversion $conversion): float => (float) ($conversion->roi ?? 0)) ?? 0.0);
                        $roasAverage = (float) ($variantGroup->avg(static fn (CampaignConversion $conversion): float => (float) ($conversion->roas ?? 0)) ?? 0.0);

                        return [
                            'campaign_id'      => $campaignId,
                            'campaign_name'    => $campaignName,
                            'variant'          => $variantName,
                            'conversions'      => $variantGroup->count(),
                            'conversion_value' => round($conversionValue, 2),
                            'average_roi'      => round($roiAverage, 2),
                            'average_roas'     => round($roasAverage, 2),
                        ];
                    })
                    ->values();
            })
            ->values();

        $multiVariantCampaigns = $variantPerformance
            ->groupBy('campaign_id')
            ->filter(static fn ($variants): bool => $variants->count() > 1)
            ->count();

        $winningVariant = $variantPerformance
            ->sort(static function (array $a, array $b): int {
                $valueComparison = $b['conversion_value'] <=> $a['conversion_value'];

                if ($valueComparison !== 0) {
                    return $valueComparison;
                }

                return $b['conversions'] <=> $a['conversions'];
            })
            ->first();

        // Assemble the final analytics payload grouping insights by marketing theme.
        $analytics = [
            'period' => [
                'requested'  => $periodPayload['raw'],
                'days'       => $period,
                'label'      => $periodPayload['label'],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => $now->format('Y-m-d'),
            ],
            'totals' => [
                'campaigns_created' => $campaigns->count(),
                'campaigns_started' => Campaign::query()
                    ->withoutGlobalScopes()
                    // Exclude future launches so the metric reflects historical starts only.
                    ->whereBetween('starts_at', [$startDate, $now])
                    ->count(),
                'campaigns_completed' => Campaign::query()
                    ->withoutGlobalScopes()
                    // Only count completions that wrapped up during the window.
                    ->whereBetween('ends_at', [$startDate, $now])
                    ->where('status', 'completed')
                    ->count(),
                'active_campaigns' => Campaign::query()
                    ->withoutGlobalScopes()
                    ->where('status', 'active')
                    // Clamp to active campaigns that have started and not yet lapsed relative to the audit window.
                    ->where(function ($query) use ($now): void {
                        $query
                            ->whereNull('starts_at')
                            ->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function ($query) use ($startDate): void {
                        $query
                            ->whereNull('ends_at')
                            ->orWhere('ends_at', '>=', $startDate);
                    })
                    ->count(),
            ],
            'insights' => [
                'views_clicks' => [
                    'title'       => 'Views & Clicks',
                    'description' => 'Campaign engagement',
                    'metrics'     => [
                        'total_views'                => $totalViews,
                        'total_clicks'               => $totalClicks,
                        'average_click_through_rate' => round((float) $averageCtr, 2),
                        'top_campaigns'              => $topEngagementCampaigns,
                    ],
                ],
                'conversions' => [
                    'title'       => 'Conversions',
                    'description' => 'Attribution modeling',
                    'metrics'     => [
                        'total_conversions'         => $totalConversions,
                        'average_conversion_rate'   => round($averageConversionRate, 2),
                        'verified_conversions'      => $verifiedConversions,
                        'attributed_conversions'    => $attributedConversions,
                        'assisted_conversions'      => $assistedConversions,
                        'assisted_conversion_value' => round($assistedConversionValue, 2),
                        'attribution_breakdown'     => $attributionBreakdown,
                    ],
                ],
                'roi_tracking' => [
                    'title'       => 'ROI Tracking',
                    'description' => 'Return on ad spend',
                    'metrics'     => [
                        'total_revenue'               => round($totalRevenue, 2),
                        'total_budget'                => round($totalBudget, 2),
                        'roi_percentage'              => $roiPercentage,
                        'roas'                        => $roas,
                        'average_roi'                 => round($averageRoi, 2),
                        'average_roas'                => round($averageRoas, 2),
                        'average_cost_per_conversion' => round($averageCostPerConversion, 2),
                    ],
                ],
                'customer_journey' => [
                    'title'       => 'Customer Journey',
                    'description' => 'Touchpoint analysis',
                    'metrics'     => [
                        'average_touchpoints'  => round($averageTouchpoints, 2),
                        'average_time_on_site' => round($averageTimeOnSite, 2),
                        'average_page_views'   => round($averagePageViews, 2),
                        'funnel_breakdown'     => $funnelBreakdown,
                    ],
                ],
                'a_b_testing' => [
                    'title'       => 'A/B Testing',
                    'description' => 'Multi-variant campaigns',
                    'metrics'     => [
                        'multi_variant_campaigns' => $multiVariantCampaigns,
                        'winning_variant'         => $winningVariant,
                        'variant_performance'     => $variantPerformance->take(10)->values()->all(),
                    ],
                ],
                'types' => [
                    'title'       => 'Campaign Types',
                    'description' => 'Channel mix distribution',
                    'metrics'     => [
                        'type_breakdown'       => $typeBreakdown,
                        'legacy_type_breakdown' => $typeBreakdown,
                    ],
                ],
            ],
            'type_breakdown' => $typeBreakdown,
            'typeBreakdown'  => $typeBreakdown,
        ];

        return response()->json(['success' => true, 'data' => $analytics]);
    }

    /**
     * Convert period request input into a clamped numeric window with helpful metadata.
     *
     * @return array{raw:string,days:int,label:string}
     */
    private function normalisePeriod(mixed $periodInput): array
    {
        $raw = is_scalar($periodInput) ? (string) $periodInput : '30';
        $normalized = null;

        if (is_numeric($periodInput)) {
            $normalized = (int) $periodInput;
        }

        if ($normalized === null && is_string($raw)) {
            if (preg_match('/(\d+)/', $raw, $matches) === 1) {
                $normalized = (int) $matches[1];
            } else {
                $keyword = Str::of($raw)->lower()->value();

                $normalized = match ($keyword) {
                    'week', '7d', 'last-week'        => 7,
                    'fortnight', '14d'               => 14,
                    'month', '30d', 'last-month'     => 30,
                    'quarter', '90d', 'last-quarter' => 90,
                    'year', '365d', 'last-year'      => 365,
                    default                          => 30,
                };
            }
        }

        $normalized ??= 30;
        $normalized = max(1, min(365, $normalized));

        return [
            'raw'   => $raw,
            'days'  => $normalized,
            'label' => sprintf('Last %d days', $normalized),
        ];
    }

    /**
     * Handle getCampaignComparison functionality with proper error handling.
     */
    #[OA\Get(
        path: '/campaigns/api/compare',
        summary: 'Compare multiple campaigns by their KPIs.',
        tags: ['Campaigns'],
        parameters: [
            new OA\QueryParameter(
                name: 'campaign_ids',
                description: 'Campaign identifiers to compare.',
                required: true,
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items(type: 'integer'),
                ),
                style: 'form',
                explode: true,
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/CampaignComparison'),
            new OA\Response(response: 400, ref: '#/components/responses/CampaignComparisonError'),
        ]
    )]
    public function getCampaignComparison(Request $request): JsonResponse
    {
        $campaignIds = $request->input('campaign_ids', []);
        if (empty($campaignIds)) {
            return response()->json(['success' => false, 'message' => __('campaigns.messages.no_campaigns_selected')], 400);
        }
        $campaigns = Campaign::whereIn('id', $campaignIds)->get(['id', 'name', 'type', 'status', 'total_views', 'total_clicks', 'total_conversions', 'total_revenue', 'conversion_rate', 'budget']);
        $comparison = $campaigns->map(function ($campaign) {
            return ['id' => $campaign->id, 'name' => $campaign->name, 'type' => $campaign->type, 'type_label' => __('campaigns.types.' . $campaign->type), 'status' => $campaign->status, 'status_label' => __('campaigns.status.' . $campaign->status), 'views' => $campaign->total_views, 'clicks' => $campaign->total_clicks, 'conversions' => $campaign->total_conversions, 'revenue' => $campaign->total_revenue, 'conversion_rate' => $campaign->conversion_rate, 'click_through_rate' => $campaign->getClickThroughRate(), 'roi' => $campaign->getROI(), 'performance_score' => $campaign->performance_score, 'performance_grade' => $campaign->performance_grade, 'budget' => $campaign->budget, 'budget_utilization' => $campaign->budget_utilization];
        });

        return response()->json(['success' => true, 'data' => $comparison]);
    }

    /**
     * Handle getCampaignRecommendations functionality with proper error handling.
     */
    #[OA\Get(
        path: '/campaigns/{campaign}/recommendations',
        summary: 'Generate performance recommendations for a campaign.',
        tags: ['Campaigns'],
        parameters: [
            new OA\PathParameter(
                name: 'campaign',
                description: 'Campaign identifier or slug.',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/CampaignRecommendations'),
        ]
    )]
    public function getCampaignRecommendations(Campaign $campaign): JsonResponse
    {
        $recommendations = [];
        // Performance-based recommendations
        if ($campaign->getConversionRate() < 2) {
            $recommendations[] = ['type' => 'performance', 'priority' => 'high', 'title' => __('campaigns.recommendations.low_conversion_rate.title'), 'description' => __('campaigns.recommendations.low_conversion_rate.description'), 'action' => 'optimize_content'];
        }
        if ($campaign->getClickThroughRate() < 1) {
            $recommendations[] = ['type' => 'performance', 'priority' => 'medium', 'title' => __('campaigns.recommendations.low_ctr.title'), 'description' => __('campaigns.recommendations.low_ctr.description'), 'action' => 'improve_targeting'];
        }
        // Budget-based recommendations
        if ($campaign->budget_utilization > 90) {
            $recommendations[] = ['type' => 'budget', 'priority' => 'high', 'title' => __('campaigns.recommendations.high_budget_utilization.title'), 'description' => __('campaigns.recommendations.high_budget_utilization.description'), 'action' => 'monitor_budget'];
        }
        // Time-based recommendations
        if ($campaign->days_remaining && $campaign->days_remaining <= 7) {
            $recommendations[] = ['type' => 'time', 'priority' => 'medium', 'title' => __('campaigns.recommendations.campaign_ending_soon.title'), 'description' => __('campaigns.recommendations.campaign_ending_soon.description'), 'action' => 'extend_campaign'];
        }
        // Content-based recommendations
        $contentSummary = $campaign->getContentSummary();
        if (! $contentSummary['has_cta']) {
            $recommendations[] = ['type' => 'content', 'priority' => 'medium', 'title' => __('campaigns.recommendations.missing_cta.title'), 'description' => __('campaigns.recommendations.missing_cta.description'), 'action' => 'add_cta'];
        }
        if ($contentSummary['content_length'] < 100) {
            $recommendations[] = ['type' => 'content', 'priority' => 'low', 'title' => __('campaigns.recommendations.short_content.title'), 'description' => __('campaigns.recommendations.short_content.description'), 'action' => 'expand_content'];
        }

        return response()->json(['success' => true, 'data' => $recommendations]);
    }
}
