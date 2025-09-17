<?php

declare (strict_types=1);
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
/**
 * CampaignController
 * 
 * HTTP controller handling CampaignController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class CampaignController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $campaigns = Campaign::query()->active()->byPriority()->with(['targetCategories', 'targetProducts', 'channel', 'zone'])->when($request->filled('type'), function ($query) use ($request) {
            return $query->where('type', $request->get('type'));
        })->when($request->filled('category'), function ($query) use ($request) {
            return $query->whereHas('targetCategories', function ($q) use ($request) {
                $q->where('slug', $request->get('category'));
            });
        })->when($request->filled('search'), function ($query) use ($request) {
            return $query->where('name', 'like', '%' . $request->get('search') . '%');
        })->paginate(12);
        return view('campaigns.index', compact('campaigns'));
    }
    /**
     * Display the specified resource with related data.
     * @param Campaign $campaign
     * @return View
     */
    public function show(Campaign $campaign): View
    {
        // Record view for analytics
        $campaign->recordView(session()->getId(), request()->ip(), request()->userAgent(), request()->header('referer'), auth()->id());
        $campaign->load(['targetCategories', 'targetProducts', 'targetCustomerGroups', 'channel', 'zone', 'discounts']);
        // Get related campaigns
        $relatedCampaigns = Campaign::query()->active()->where('id', '!=', $campaign->id)->whereHas('targetCategories', function ($query) use ($campaign) {
            $query->whereIn('categories.id', $campaign->targetCategories->pluck('id'));
        })->limit(4)->get();
        return view('campaigns.show', compact('campaign', 'relatedCampaigns'));
    }
    /**
     * Handle click functionality with proper error handling.
     * @param Request $request
     * @param Campaign $campaign
     * @return JsonResponse
     */
    public function click(Request $request, Campaign $campaign): JsonResponse
    {
        $clickType = $request->get('type', 'cta');
        $clickedUrl = $request->get('url');
        $campaign->recordClick($clickType, $clickedUrl, session()->getId(), request()->ip(), request()->userAgent(), auth()->id());
        return response()->json(['success' => true, 'message' => __('campaigns.messages.click_recorded')]);
    }
    /**
     * Handle conversion functionality with proper error handling.
     * @param Request $request
     * @param Campaign $campaign
     * @return JsonResponse
     */
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
     * @return View
     */
    public function featured(): View
    {
        $campaigns = Campaign::query()->featured()->active()->byPriority()->with(['targetCategories', 'channel'])->limit(6)->get();
        return view('campaigns.featured', compact('campaigns'));
    }
    /**
     * Handle byType functionality with proper error handling.
     * @param Request $request
     * @param string $type
     * @return View
     */
    public function byType(Request $request, string $type): View
    {
        $campaigns = Campaign::query()->active()->where('type', $type)->byPriority()->with(['targetCategories', 'targetProducts', 'channel'])->paginate(12);
        return view('campaigns.by-type', compact('campaigns', 'type'));
    }
    /**
     * Handle search functionality with proper error handling.
     * @param Request $request
     * @return View
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
     * @return JsonResponse
     */
    public function getCampaignStatistics(): JsonResponse
    {
        $statistics = ['total_campaigns' => Campaign::count(), 'active_campaigns' => Campaign::active()->count(), 'scheduled_campaigns' => Campaign::scheduled()->count(), 'completed_campaigns' => Campaign::where('status', 'completed')->count(), 'total_views' => Campaign::sum('total_views'), 'total_clicks' => Campaign::sum('total_clicks'), 'total_conversions' => Campaign::sum('total_conversions'), 'total_revenue' => Campaign::sum('total_revenue'), 'average_conversion_rate' => Campaign::where('total_views', '>', 0)->avg('conversion_rate') ?? 0, 'average_click_through_rate' => Campaign::where('total_views', '>', 0)->avg(\DB::raw('(total_clicks / total_views) * 100')) ?? 0, 'average_roi' => Campaign::where('budget', '>', 0)->avg(\DB::raw('((total_revenue - budget) / budget) * 100')) ?? 0];
        return response()->json(['success' => true, 'data' => $statistics]);
    }
    /**
     * Handle getCampaignTypes functionality with proper error handling.
     * @return JsonResponse
     */
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
                'email' => 'heroicon-o-envelope',
                'sms' => 'heroicon-o-device-phone-mobile',
                'push' => 'heroicon-o-bell',
                'banner' => 'heroicon-o-photo',
                'popup' => 'heroicon-o-window',
                'social' => 'heroicon-o-share',
                default => 'heroicon-o-megaphone',
            }, 'color' => match ($type) {
                'email' => 'blue',
                'sms' => 'green',
                'push' => 'yellow',
                'banner' => 'purple',
                'popup' => 'pink',
                'social' => 'red',
                default => 'gray',
            }];
        }
        return response()->json(['success' => true, 'data' => $formattedTypes]);
    }
    /**
     * Handle getCampaignPerformance functionality with proper error handling.
     * @return JsonResponse
     */
    public function getCampaignPerformance(): JsonResponse
    {
        $performance = ['high_performing' => Campaign::where('conversion_rate', '>', 5)->count(), 'medium_performing' => Campaign::whereBetween('conversion_rate', [2, 5])->count(), 'low_performing' => Campaign::where('conversion_rate', '<', 2)->count(), 'needs_attention' => Campaign::where(function ($query) {
            $query->where('conversion_rate', '<', 2)->orWhere('total_views', '>', 0)->whereRaw('(total_clicks / total_views) < 0.01');
        })->count()];
        return response()->json(['success' => true, 'data' => $performance]);
    }
    /**
     * Handle getCampaignAnalytics functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function getCampaignAnalytics(Request $request): JsonResponse
    {
        $period = $request->input('period', '30');
        // days
        $startDate = now()->subDays($period);
        $analytics = ['period' => $period, 'start_date' => $startDate->format('Y-m-d'), 'end_date' => now()->format('Y-m-d'), 'campaigns_created' => Campaign::where('created_at', '>=', $startDate)->count(), 'campaigns_started' => Campaign::where('start_date', '>=', $startDate)->count(), 'campaigns_completed' => Campaign::where('end_date', '>=', $startDate)->where('status', 'completed')->count(), 'total_views' => Campaign::where('created_at', '>=', $startDate)->sum('total_views'), 'total_clicks' => Campaign::where('created_at', '>=', $startDate)->sum('total_clicks'), 'total_conversions' => Campaign::where('created_at', '>=', $startDate)->sum('total_conversions'), 'total_revenue' => Campaign::where('created_at', '>=', $startDate)->sum('total_revenue'), 'top_performing_campaigns' => Campaign::where('created_at', '>=', $startDate)->orderBy('conversion_rate', 'desc')->limit(5)->get(['id', 'name', 'type', 'conversion_rate', 'total_revenue']), 'campaign_types_breakdown' => Campaign::where('created_at', '>=', $startDate)->selectRaw('type, COUNT(*) as count, AVG(conversion_rate) as avg_conversion_rate')->groupBy('type')->get()];
        return response()->json(['success' => true, 'data' => $analytics]);
    }
    /**
     * Handle getCampaignComparison functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
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
     * @param Campaign $campaign
     * @return JsonResponse
     */
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
        if (!$contentSummary['has_cta']) {
            $recommendations[] = ['type' => 'content', 'priority' => 'medium', 'title' => __('campaigns.recommendations.missing_cta.title'), 'description' => __('campaigns.recommendations.missing_cta.description'), 'action' => 'add_cta'];
        }
        if ($contentSummary['content_length'] < 100) {
            $recommendations[] = ['type' => 'content', 'priority' => 'low', 'title' => __('campaigns.recommendations.short_content.title'), 'description' => __('campaigns.recommendations.short_content.description'), 'action' => 'expand_content'];
        }
        return response()->json(['success' => true, 'data' => $recommendations]);
    }
}