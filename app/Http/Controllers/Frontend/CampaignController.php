<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CampaignController extends Controller
{
    public function index(Request $request): View
    {
        $campaigns = Campaign::query()
            ->active()
            ->byPriority()
            ->with(['targetCategories', 'targetProducts', 'channel', 'zone'])
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->get('type'));
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                return $query->whereHas('targetCategories', function ($q) use ($request) {
                    $q->where('slug', $request->get('category'));
                });
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                return $query->where('name', 'like', '%'.$request->get('search').'%');
            })
            ->paginate(12);

        return view('frontend.campaigns.index', compact('campaigns'));
    }

    public function show(Campaign $campaign): View
    {
        // Record view for analytics
        $campaign->recordView(
            session()->getId(),
            request()->ip(),
            request()->userAgent(),
            request()->header('referer'),
            auth()->id()
        );

        $campaign->load([
            'targetCategories',
            'targetProducts',
            'targetCustomerGroups',
            'channel',
            'zone',
            'discounts',
        ]);

        // Get related campaigns
        $relatedCampaigns = Campaign::query()
            ->active()
            ->where('id', '!=', $campaign->id)
            ->whereHas('targetCategories', function ($query) use ($campaign) {
                $query->whereIn('categories.id', $campaign->targetCategories->pluck('id'));
            })
            ->limit(4)
            ->get();

        return view('frontend.campaigns.show', compact('campaign', 'relatedCampaigns'));
    }

    public function click(Request $request, Campaign $campaign): JsonResponse
    {
        $clickType = $request->get('type', 'cta');
        $clickedUrl = $request->get('url');

        $campaign->recordClick(
            $clickType,
            $clickedUrl,
            session()->getId(),
            request()->ip(),
            request()->userAgent(),
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => __('campaigns.messages.click_recorded'),
        ]);
    }

    public function conversion(Request $request, Campaign $campaign): JsonResponse
    {
        $conversionType = $request->get('type', 'purchase');
        $conversionValue = (float) $request->get('value', 0);
        $orderId = $request->get('order_id');
        $conversionData = $request->get('data', []);

        $campaign->recordConversion(
            $conversionType,
            $conversionValue,
            $orderId,
            auth()->id(),
            session()->getId(),
            $conversionData
        );

        return response()->json([
            'success' => true,
            'message' => __('campaigns.messages.conversion_recorded'),
        ]);
    }

    public function featured(): View
    {
        $campaigns = Campaign::query()
            ->featured()
            ->active()
            ->byPriority()
            ->with(['targetCategories', 'channel'])
            ->limit(6)
            ->get();

        return view('frontend.campaigns.featured', compact('campaigns'));
    }

    public function byType(Request $request, string $type): View
    {
        $campaigns = Campaign::query()
            ->active()
            ->where('type', $type)
            ->byPriority()
            ->with(['targetCategories', 'targetProducts', 'channel'])
            ->paginate(12);

        return view('frontend.campaigns.by-type', compact('campaigns', 'type'));
    }

    public function search(Request $request): View
    {
        $query = $request->get('q');

        $campaigns = Campaign::query()
            ->active()
            ->when($query, function ($q) use ($query) {
                return $q
                    ->where('name', 'like', '%'.$query.'%')
                    ->orWhere('description', 'like', '%'.$query.'%');
            })
            ->byPriority()
            ->with(['targetCategories', 'channel'])
            ->paginate(12);

        return view('frontend.campaigns.search', compact('campaigns', 'query'));
    }
}
