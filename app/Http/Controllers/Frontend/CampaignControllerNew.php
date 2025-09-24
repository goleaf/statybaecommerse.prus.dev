<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CampaignControllerNew
 *
 * HTTP controller handling CampaignControllerNew related web requests, responses, and business logic with proper validation and error handling.
 */
final class CampaignControllerNew extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        $query = Campaign::active()->with(['targetCategories', 'targetProducts', 'targetCustomerGroups'])->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))->when($request->filled('category'), fn ($q) => $q->whereHas('targetCategories', fn ($subQ) => $subQ->where('categories.id', $request->category)))->when($request->filled('product'), fn ($q) => $q->whereHas('targetProducts', fn ($subQ) => $subQ->where('products.id', $request->product)))->when($request->filled('customer_group'), fn ($q) => $q->whereHas('targetCustomerGroups', fn ($subQ) => $subQ->where('customer_groups.id', $request->customer_group)))->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))->when($request->filled('featured'), fn ($q) => $q->featured())->when($request->filled('budget_min'), fn ($q) => $q->where('budget', '>=', $request->budget_min))->when($request->filled('budget_max'), fn ($q) => $q->where('budget', '<=', $request->budget_max));
        $campaigns = $query->orderBy('display_priority', 'desc')->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc')->paginate(12);
        $categories = Category::whereHas('campaigns')->get();
        $products = Product::whereHas('campaigns')->get();
        $customerGroups = CustomerGroup::whereHas('campaigns')->get();

        return view('campaigns.index', compact('campaigns', 'categories', 'products', 'customerGroups'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Campaign $campaign): View
    {
        if (! $campaign->isActive()) {
            abort(404);
        }
        // Track view
        $campaign->recordView(sessionId: session()->getId(), ipAddress: request()->ip(), userAgent: request()->userAgent(), referer: request()->header('referer'), customerId: auth()->id());
        // Load related campaigns
        $relatedCampaigns = Campaign::active()->where('id', '!=', $campaign->id)->where(function ($q) use ($campaign) {
            $q->whereHas('targetCategories', fn ($subQ) => $subQ->whereIn('categories.id', $campaign->targetCategories->pluck('id')))->orWhereHas('targetProducts', fn ($subQ) => $subQ->whereIn('products.id', $campaign->targetProducts->pluck('id')))->orWhere('type', $campaign->type);
        })->limit(4)->get();

        return view('campaigns.show', compact('campaign', 'relatedCampaigns'));
    }

    /**
     * Handle click functionality with proper error handling.
     */
    public function click(Campaign $campaign, Request $request): JsonResponse
    {
        if (! $campaign->isActive()) {
            abort(404);
        }
        $validated = $request->validate(['click_type' => 'string|in:cta,banner,link', 'clicked_url' => 'nullable|url']);
        $campaign->recordClick(clickType: $validated['click_type'] ?? 'cta', clickedUrl: $validated['clicked_url'] ?? $campaign->cta_url, sessionId: session()->getId(), ipAddress: request()->ip(), userAgent: request()->userAgent(), customerId: auth()->id());

        return response()->json(['success' => true, 'message' => __('campaigns.frontend.click_recorded'), 'campaign' => ['id' => $campaign->id, 'name' => $campaign->name, 'total_clicks' => $campaign->fresh()->total_clicks]]);
    }

    /**
     * Handle conversion functionality with proper error handling.
     */
    public function conversion(Campaign $campaign, Request $request): JsonResponse
    {
        if (! $campaign->isActive()) {
            abort(404);
        }
        $validated = $request->validate(['conversion_type' => 'string|in:purchase,signup,download,subscription', 'conversion_value' => 'numeric|min:0', 'order_id' => 'nullable|exists:orders,id', 'conversion_data' => 'nullable|array']);
        $campaign->recordConversion(conversionType: $validated['conversion_type'] ?? 'purchase', conversionValue: $validated['conversion_value'] ?? 0, orderId: $validated['order_id'] ?? null, customerId: auth()->id(), sessionId: session()->getId(), conversionData: $validated['conversion_data'] ?? []);

        return response()->json(['success' => true, 'message' => __('campaigns.frontend.conversion_recorded'), 'campaign' => ['id' => $campaign->id, 'name' => $campaign->name, 'total_conversions' => $campaign->fresh()->total_conversions, 'total_revenue' => $campaign->fresh()->total_revenue]]);
    }

    /**
     * Handle featured functionality with proper error handling.
     */
    public function featured(): View
    {
        $campaigns = Campaign::active()->featured()->with(['targetCategories', 'targetProducts'])->orderBy('display_priority', 'desc')->orderBy('created_at', 'desc')->paginate(12);

        return view('campaigns.featured', compact('campaigns'));
    }

    /**
     * Handle byType functionality with proper error handling.
     */
    public function byType(string $type): View
    {
        $validTypes = ['email', 'sms', 'push', 'banner', 'popup', 'social'];
        if (! in_array($type, $validTypes)) {
            abort(404);
        }
        $campaigns = Campaign::active()->where('type', $type)->with(['targetCategories', 'targetProducts'])->orderBy('display_priority', 'desc')->orderBy('created_at', 'desc')->paginate(12);

        return view('campaigns.by-type', compact('campaigns', 'type'));
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(Request $request): View
    {
        $query = $request->input('q', '');
        if (empty($query)) {
            return redirect()->route('campaigns.index');
        }
        $campaigns = Campaign::active()->where(function ($q) use ($query) {
            $q->where('name', 'like', '%'.$query.'%')->orWhere('description', 'like', '%'.$query.'%')->orWhere('content', 'like', '%'.$query.'%');
        })->with(['targetCategories', 'targetProducts'])->orderBy('display_priority', 'desc')->orderBy('created_at', 'desc')->paginate(12);

        return view('campaigns.search', compact('campaigns', 'query'));
    }

    /**
     * Handle api functionality with proper error handling.
     */
    public function api(Request $request): JsonResponse
    {
        $campaigns = Campaign::active()->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))->when($request->filled('featured'), fn ($q) => $q->featured())->when($request->filled('limit'), fn ($q) => $q->limit($request->limit))->orderBy('display_priority', 'desc')->orderBy('created_at', 'desc')->get()->map(function ($campaign) {
            return ['id' => $campaign->id, 'name' => $campaign->name, 'slug' => $campaign->slug, 'description' => $campaign->description, 'type' => $campaign->type, 'status' => $campaign->status, 'budget' => $campaign->budget, 'start_date' => $campaign->start_date?->toISOString(), 'end_date' => $campaign->end_date?->toISOString(), 'cta_text' => $campaign->cta_text, 'cta_url' => $campaign->cta_url, 'is_featured' => $campaign->is_featured, 'total_views' => $campaign->total_views, 'total_clicks' => $campaign->total_clicks, 'total_conversions' => $campaign->total_conversions, 'conversion_rate' => $campaign->conversion_rate, 'image_url' => $campaign->getImageUrl('medium'), 'banner_url' => $campaign->getBannerUrl('medium')];
        });

        return response()->json(['data' => $campaigns, 'meta' => ['total' => $campaigns->count(), 'types' => Campaign::active()->distinct()->pluck('type')]]);
    }

    /**
     * Handle apiShow functionality with proper error handling.
     */
    public function apiShow(Campaign $campaign): JsonResponse
    {
        if (! $campaign->isActive()) {
            abort(404);
        }
        // Track API view
        $campaign->recordView(sessionId: session()->getId(), ipAddress: request()->ip(), userAgent: request()->userAgent(), referer: request()->header('referer'), customerId: auth()->id());

        return response()->json(['data' => ['id' => $campaign->id, 'name' => $campaign->name, 'slug' => $campaign->slug, 'description' => $campaign->description, 'type' => $campaign->type, 'status' => $campaign->status, 'subject' => $campaign->subject, 'content' => $campaign->content, 'budget' => $campaign->budget, 'budget_limit' => $campaign->budget_limit, 'start_date' => $campaign->start_date?->toISOString(), 'end_date' => $campaign->end_date?->toISOString(), 'cta_text' => $campaign->cta_text, 'cta_url' => $campaign->cta_url, 'is_featured' => $campaign->is_featured, 'display_priority' => $campaign->display_priority, 'total_views' => $campaign->total_views, 'total_clicks' => $campaign->total_clicks, 'total_conversions' => $campaign->total_conversions, 'total_revenue' => $campaign->total_revenue, 'conversion_rate' => $campaign->conversion_rate, 'click_through_rate' => $campaign->getClickThroughRate(), 'roi' => $campaign->getROI(), 'image_url' => $campaign->getImageUrl('large'), 'banner_url' => $campaign->getBannerUrl('large'), 'target_categories' => $campaign->targetCategories->map(fn ($cat) => ['id' => $cat->id, 'name' => $cat->name, 'slug' => $cat->slug]), 'target_products' => $campaign->targetProducts->map(fn ($prod) => ['id' => $prod->id, 'name' => $prod->name, 'slug' => $prod->slug]), 'target_customer_groups' => $campaign->targetCustomerGroups->map(fn ($group) => ['id' => $group->id, 'name' => $group->name]), 'meta_title' => $campaign->meta_title, 'meta_description' => $campaign->meta_description, 'social_media_ready' => $campaign->social_media_ready, 'created_at' => $campaign->created_at->toISOString(), 'updated_at' => $campaign->updated_at->toISOString()]]);
    }
}
