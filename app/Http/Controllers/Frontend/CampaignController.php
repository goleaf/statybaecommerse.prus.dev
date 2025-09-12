<?php declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignView;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class CampaignController extends Controller
{
    public function index(Request $request): View
    {
        $campaigns = $this->getActiveCampaigns($request);
        
        return view('frontend.campaigns.index', compact('campaigns'));
    }

    public function show(Request $request, Campaign $campaign): View
    {
        // Record view
        $this->recordCampaignView($campaign, $request);
        
        // Get related campaigns
        $relatedCampaigns = $this->getRelatedCampaigns($campaign);
        
        // Get campaign products
        $products = $this->getCampaignProducts($campaign);
        
        return view('frontend.campaigns.show', compact('campaign', 'relatedCampaigns', 'products'));
    }

    public function click(Request $request, Campaign $campaign): JsonResponse
    {
        $clickType = $request->input('type', 'cta');
        $clickedUrl = $request->input('url');
        
        $this->recordCampaignClick($campaign, $request, $clickType, $clickedUrl);
        
        return response()->json([
            'success' => true,
            'message' => __('campaigns.click_recorded'),
        ]);
    }

    public function conversion(Request $request, Campaign $campaign): JsonResponse
    {
        $conversionType = $request->input('type', 'purchase');
        $conversionValue = (float) $request->input('value', 0);
        $orderId = $request->input('order_id');
        
        $this->recordCampaignConversion($campaign, $request, $conversionType, $conversionValue, $orderId);
        
        return response()->json([
            'success' => true,
            'message' => __('campaigns.conversion_recorded'),
        ]);
    }

    public function featured(): View
    {
        $campaigns = Cache::remember('featured_campaigns', 300, function () {
            return Campaign::featured()
                ->active()
                ->byPriority()
                ->with(['channel', 'zone'])
                ->limit(6)
                ->get();
        });
        
        return view('frontend.campaigns.featured', compact('campaigns'));
    }

    public function analytics(Request $request, Campaign $campaign): View
    {
        $this->authorize('view', $campaign);
        
        $analytics = $this->getCampaignAnalytics($campaign);
        
        return view('frontend.campaigns.analytics', compact('campaign', 'analytics'));
    }

    public function products(Request $request, Campaign $campaign): View
    {
        $products = $this->getCampaignProducts($campaign, $request);
        
        return view('frontend.campaigns.products', compact('campaign', 'products'));
    }

    private function getActiveCampaigns(Request $request)
    {
        $query = Campaign::active()
            ->with(['channel', 'zone'])
            ->orderBy('display_priority', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by channel
        if ($request->has('channel')) {
            $query->where('channel_id', $request->input('channel'));
        }

        // Filter by zone
        if ($request->has('zone')) {
            $query->where('zone_id', $request->input('zone'));
        }

        // Filter by category
        if ($request->has('category')) {
            $query->whereJsonContains('target_categories', (int) $request->input('category'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->paginate(12);
    }

    private function getRelatedCampaigns(Campaign $campaign)
    {
        return Campaign::active()
            ->where('id', '!=', $campaign->id)
            ->where(function ($query) use ($campaign) {
                $query->where('channel_id', $campaign->channel_id)
                      ->orWhere('zone_id', $campaign->zone_id)
                      ->orWhereJsonContains('target_categories', $campaign->target_categories);
            })
            ->limit(4)
            ->get();
    }

    private function getCampaignProducts(Campaign $campaign, Request $request = null)
    {
        $query = Product::query();

        // Filter by campaign target products
        if ($campaign->target_products) {
            $query->whereIn('id', $campaign->target_products);
        }

        // Filter by campaign target categories
        if ($campaign->target_categories) {
            $query->orWhereIn('category_id', $campaign->target_categories);
        }

        // Apply additional filters from request
        if ($request) {
            if ($request->has('category')) {
                $query->where('category_id', $request->input('category'));
            }

            if ($request->has('price_min')) {
                $query->where('price', '>=', $request->input('price_min'));
            }

            if ($request->has('price_max')) {
                $query->where('price', '<=', $request->input('price_max'));
            }

            if ($request->has('sort')) {
                $sort = $request->input('sort');
                match ($sort) {
                    'price_asc' => $query->orderBy('price', 'asc'),
                    'price_desc' => $query->orderBy('price', 'desc'),
                    'name_asc' => $query->orderBy('name', 'asc'),
                    'name_desc' => $query->orderBy('name', 'desc'),
                    'newest' => $query->orderBy('created_at', 'desc'),
                    default => $query->orderBy('name', 'asc'),
                };
            }
        }

        return $query->with(['category', 'media'])
                    ->paginate(20);
    }

    private function recordCampaignView(Campaign $campaign, Request $request): void
    {
        if (!$campaign->track_conversions) {
            return;
        }

        $sessionId = $request->session()->getId();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $referer = $request->header('referer');
        $customerId = auth()->id();

        // Check if view already recorded for this session
        $existingView = CampaignView::where('campaign_id', $campaign->id)
            ->where('session_id', $sessionId)
            ->whereDate('viewed_at', now()->toDateString())
            ->first();

        if (!$existingView) {
            $campaign->recordView($sessionId, $ipAddress, $userAgent, $referer, $customerId);
        }
    }

    private function recordCampaignClick(Campaign $campaign, Request $request, string $clickType, ?string $clickedUrl): void
    {
        if (!$campaign->track_conversions) {
            return;
        }

        $sessionId = $request->session()->getId();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $customerId = auth()->id();

        $campaign->recordClick($clickType, $clickedUrl, $sessionId, $ipAddress, $userAgent, $customerId);
    }

    private function recordCampaignConversion(Campaign $campaign, Request $request, string $conversionType, float $conversionValue, ?int $orderId): void
    {
        if (!$campaign->track_conversions) {
            return;
        }

        $sessionId = $request->session()->getId();
        $customerId = auth()->id();
        $conversionData = [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
        ];

        $campaign->recordConversion($conversionType, $conversionValue, $orderId, $customerId, $sessionId, $conversionData);
    }

    private function getCampaignAnalytics(Campaign $campaign): array
    {
        $analytics = [
            'overview' => [
                'total_views' => $campaign->total_views,
                'total_clicks' => $campaign->total_clicks,
                'total_conversions' => $campaign->total_conversions,
                'total_revenue' => $campaign->total_revenue,
                'conversion_rate' => $campaign->getConversionRate(),
                'click_through_rate' => $campaign->getClickThroughRate(),
                'roi' => $campaign->getROI(),
            ],
            'daily_stats' => $this->getDailyStats($campaign),
            'top_products' => $this->getTopProducts($campaign),
            'conversion_sources' => $this->getConversionSources($campaign),
        ];

        return $analytics;
    }

    private function getDailyStats(Campaign $campaign): array
    {
        return DB::table('campaign_views')
            ->select(
                DB::raw('DATE(viewed_at) as date'),
                DB::raw('COUNT(*) as views'),
                DB::raw('(SELECT COUNT(*) FROM campaign_clicks WHERE campaign_id = ? AND DATE(clicked_at) = DATE(campaign_views.viewed_at)) as clicks', [$campaign->id]),
                DB::raw('(SELECT COUNT(*) FROM campaign_conversions WHERE campaign_id = ? AND DATE(converted_at) = DATE(campaign_views.viewed_at)) as conversions', [$campaign->id])
            )
            ->where('campaign_id', $campaign->id)
            ->where('viewed_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();
    }

    private function getTopProducts(Campaign $campaign): array
    {
        return DB::table('campaign_conversions')
            ->join('orders', 'campaign_conversions.order_id', '=', 'orders.id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_revenue')
            )
            ->where('campaign_conversions.campaign_id', $campaign->id)
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getConversionSources(Campaign $campaign): array
    {
        return DB::table('campaign_conversions')
            ->select(
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(conversion_data, '$.source')) as source"),
                DB::raw('COUNT(*) as conversions'),
                DB::raw('SUM(conversion_value) as revenue')
            )
            ->where('campaign_id', $campaign->id)
            ->whereNotNull(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(conversion_data, '$.source'))"))
            ->groupBy('source')
            ->orderBy('conversions', 'desc')
            ->get()
            ->toArray();
    }
}
