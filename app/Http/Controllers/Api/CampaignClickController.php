<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignClickRequest;
use App\Http\Requests\UpdateCampaignClickRequest;
use App\Http\Resources\CampaignClickCollection;
use App\Http\Resources\CampaignClickResource;
use App\Models\CampaignClick;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final /**
 * CampaignClickController
 * 
 * HTTP controller handling web requests and responses.
 */
class CampaignClickController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CampaignClick::with(['campaign', 'customer']);

        // Apply filters
        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->has('click_type')) {
            $query->where('click_type', $request->click_type);
        }

        if ($request->has('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        if ($request->has('is_converted')) {
            $query->where('is_converted', $request->boolean('is_converted'));
        }

        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        if ($request->has('utm_source')) {
            $query->where('utm_source', $request->utm_source);
        }

        if ($request->has('date_from')) {
            $query->where('clicked_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('clicked_at', '<=', $request->date_to);
        }

        // For authenticated users, show only their clicks
        if (Auth::check()) {
            $query->where('customer_id', Auth::id());
        }

        $clicks = $query->orderBy('clicked_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json(new CampaignClickCollection($clicks));
    }

    public function store(StoreCampaignClickRequest $request): JsonResponse
    {
        $click = CampaignClick::create($request->validated());

        return response()->json([
            'data' => new CampaignClickResource($click),
            'message' => __('campaign_clicks.created_successfully'),
        ], 201);
    }

    public function show(CampaignClick $campaignClick): JsonResponse
    {
        // Check if user can view this click
        if (Auth::check() && $campaignClick->customer_id !== Auth::id()) {
            return response()->json(['message' => __('campaign_clicks.unauthorized')], 403);
        }

        return response()->json([
            'data' => new CampaignClickResource($campaignClick->load(['campaign', 'customer', 'conversions'])),
        ]);
    }

    public function update(UpdateCampaignClickRequest $request, CampaignClick $campaignClick): JsonResponse
    {
        // Check if user can update this click
        if (Auth::check() && $campaignClick->customer_id !== Auth::id()) {
            return response()->json(['message' => __('campaign_clicks.unauthorized')], 403);
        }

        $campaignClick->update($request->validated());

        return response()->json([
            'data' => new CampaignClickResource($campaignClick),
            'message' => __('campaign_clicks.updated_successfully'),
        ]);
    }

    public function destroy(CampaignClick $campaignClick): JsonResponse
    {
        // Check if user can delete this click
        if (Auth::check() && $campaignClick->customer_id !== Auth::id()) {
            return response()->json(['message' => __('campaign_clicks.unauthorized')], 403);
        }

        $campaignClick->delete();

        return response()->json([
            'message' => __('campaign_clicks.deleted_successfully'),
        ], 204);
    }

    public function statistics(): JsonResponse
    {
        $query = CampaignClick::query();

        // For authenticated users, show only their statistics
        if (Auth::check()) {
            $query->where('customer_id', Auth::id());
        }

        $totalClicks = $query->count();
        $convertedClicks = $query->where('is_converted', true)->count();
        $conversionRate = $totalClicks > 0 ? round(($convertedClicks / $totalClicks) * 100, 2) : 0;
        $totalConversionValue = $query->where('is_converted', true)->sum('conversion_value');
        $todayClicks = $query->whereDate('clicked_at', today())->count();
        $thisWeekClicks = $query->whereBetween('clicked_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        return response()->json([
            'total_clicks' => $totalClicks,
            'converted_clicks' => $convertedClicks,
            'conversion_rate' => $conversionRate,
            'total_conversion_value' => $totalConversionValue,
            'today_clicks' => $todayClicks,
            'this_week_clicks' => $thisWeekClicks,
        ]);
    }

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
        $clicksOverTime = $query->select(
            DB::raw('DATE(clicked_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Device types
        $deviceTypes = $query->select('device_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();

        // Browsers
        $browsers = $query->select('browser', DB::raw('COUNT(*) as count'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Countries
        $countries = $query->select('country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // UTM sources
        $utmSources = $query->select('utm_source', DB::raw('COUNT(*) as count'))
            ->whereNotNull('utm_source')
            ->groupBy('utm_source')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'clicks_over_time' => $clicksOverTime,
            'device_types' => $deviceTypes,
            'browsers' => $browsers,
            'countries' => $countries,
            'utm_sources' => $utmSources,
        ]);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
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
        $filename = 'campaign_clicks_'.now()->format('Y-m-d_H-i-s').'.'.$format;

        if ($format === 'csv') {
            return $this->exportCsv($clicks, $filename);
        }

        return response()->json(['message' => __('campaign_clicks.unsupported_format')], 400);
    }

    private function exportCsv($clicks, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return response()->stream(function () use ($clicks) {
            $handle = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV headers
            fputcsv($handle, [
                'ID',
                __('campaign_clicks.campaign'),
                __('campaign_clicks.customer'),
                __('campaign_clicks.click_type'),
                __('campaign_clicks.clicked_url'),
                __('campaign_clicks.clicked_at'),
                __('campaign_clicks.device_type'),
                __('campaign_clicks.browser'),
                __('campaign_clicks.country'),
                __('campaign_clicks.utm_source'),
                __('campaign_clicks.converted'),
                __('campaign_clicks.conversion_value'),
            ]);

            // CSV data
            foreach ($clicks as $click) {
                fputcsv($handle, [
                    $click->id,
                    $click->campaign->name ?? '',
                    $click->customer->name ?? __('campaign_clicks.guest'),
                    $click->click_type_label,
                    $click->clicked_url,
                    $click->clicked_at->format('Y-m-d H:i:s'),
                    $click->device_type_label,
                    $click->browser_label,
                    $click->country,
                    $click->utm_source,
                    $click->is_converted ? __('campaign_clicks.yes') : __('campaign_clicks.no'),
                    $click->conversion_value,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
