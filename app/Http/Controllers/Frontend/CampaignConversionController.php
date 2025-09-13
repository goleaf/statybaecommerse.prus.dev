<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignConversion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CampaignConversionController extends Controller
{
    public function index(Request $request): View
    {
        $query = CampaignConversion::with(['campaign', 'customer', 'order'])
            ->orderBy('converted_at', 'desc');

        // Apply filters
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('conversion_type')) {
            $query->where('conversion_type', $request->conversion_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('date_from')) {
            $query->where('converted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('converted_at', '<=', $request->date_to);
        }

        if ($request->filled('min_value')) {
            $query->where('conversion_value', '>=', $request->min_value);
        }

        if ($request->filled('max_value')) {
            $query->where('conversion_value', '<=', $request->max_value);
        }

        $conversions = $query->paginate(20);

        $campaigns = Campaign::where('status', 'active')->get();
        $conversionTypes = CampaignConversion::distinct()->pluck('conversion_type');
        $statuses = CampaignConversion::distinct()->pluck('status');
        $deviceTypes = CampaignConversion::distinct()->pluck('device_type');
        $sources = CampaignConversion::distinct()->pluck('source');

        return view('campaign-conversions.index', compact(
            'conversions',
            'campaigns',
            'conversionTypes',
            'statuses',
            'deviceTypes',
            'sources'
        ));
    }

    public function show(CampaignConversion $campaignConversion): View
    {
        $campaignConversion->load(['campaign', 'customer', 'order']);

        return view('campaign-conversions.show', compact('campaignConversion'));
    }

    public function analytics(Request $request): JsonResponse
    {
        $query = CampaignConversion::query();

        // Apply date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('converted_at', [$request->date_from, $request->date_to]);
        } else {
            $query->where('converted_at', '>=', now()->subDays(30));
        }

        // Apply campaign filter
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        $conversions = $query->get();

        $analytics = [
            'total_conversions' => $conversions->count(),
            'total_value' => $conversions->sum('conversion_value'),
            'average_value' => $conversions->avg('conversion_value'),
            'conversion_rate' => $conversions->avg('conversion_rate'),
            'roi' => $conversions->avg('roi'),
            'roas' => $conversions->avg('roas'),
            'by_type' => $conversions->groupBy('conversion_type')->map->count(),
            'by_status' => $conversions->groupBy('status')->map->count(),
            'by_device' => $conversions->groupBy('device_type')->map->count(),
            'by_source' => $conversions->groupBy('source')->map->count(),
            'by_medium' => $conversions->groupBy('medium')->map->count(),
            'by_country' => $conversions->groupBy('country')->map->count(),
            'daily_trends' => $conversions->groupBy(function ($conversion) {
                return $conversion->converted_at->format('Y-m-d');
            })->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'value' => $group->sum('conversion_value'),
                ];
            }),
        ];

        return response()->json($analytics);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = CampaignConversion::with(['campaign', 'customer', 'order']);

        // Apply same filters as index
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('conversion_type')) {
            $query->where('conversion_type', $request->conversion_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('date_from')) {
            $query->where('converted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('converted_at', '<=', $request->date_to);
        }

        $conversions = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="campaign_conversions_'.now()->format('Y-m-d_H-i-s').'.csv"',
        ];

        return response()->stream(function () use ($conversions) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                'ID',
                'Campaign',
                'Customer',
                'Order',
                'Type',
                'Value',
                'Status',
                'Source',
                'Medium',
                'Device Type',
                'Country',
                'City',
                'ROI',
                'ROAS',
                'Converted At',
            ]);

            // CSV data
            foreach ($conversions as $conversion) {
                fputcsv($handle, [
                    $conversion->id,
                    $conversion->campaign?->name,
                    $conversion->customer?->email,
                    $conversion->order?->id,
                    $conversion->conversion_type,
                    $conversion->conversion_value,
                    $conversion->status,
                    $conversion->source,
                    $conversion->medium,
                    $conversion->device_type,
                    $conversion->country,
                    $conversion->city,
                    $conversion->roi,
                    $conversion->roas,
                    $conversion->converted_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function create(): View
    {
        $campaigns = Campaign::where('status', 'active')->get();
        $conversionTypes = [
            'purchase' => __('campaign_conversions.conversion_types.purchase'),
            'signup' => __('campaign_conversions.conversion_types.signup'),
            'download' => __('campaign_conversions.conversion_types.download'),
            'lead' => __('campaign_conversions.conversion_types.lead'),
            'subscription' => __('campaign_conversions.conversion_types.subscription'),
            'trial' => __('campaign_conversions.conversion_types.trial'),
            'custom' => __('campaign_conversions.conversion_types.custom'),
        ];

        $statuses = [
            'pending' => __('campaign_conversions.statuses.pending'),
            'completed' => __('campaign_conversions.statuses.completed'),
            'cancelled' => __('campaign_conversions.statuses.cancelled'),
            'refunded' => __('campaign_conversions.statuses.refunded'),
        ];

        $deviceTypes = [
            'mobile' => __('campaign_conversions.device_types.mobile'),
            'tablet' => __('campaign_conversions.device_types.tablet'),
            'desktop' => __('campaign_conversions.device_types.desktop'),
        ];

        return view('campaign-conversions.create', compact(
            'campaigns',
            'conversionTypes',
            'statuses',
            'deviceTypes'
        ));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:discount_campaigns,id',
            'conversion_type' => 'required|string|max:255',
            'conversion_value' => 'required|numeric|min:0',
            'status' => 'required|string|max:255',
            'converted_at' => 'required|date',
            'source' => 'nullable|string|max:255',
            'medium' => 'nullable|string|max:255',
            'device_type' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'custom_attributes' => 'nullable|array',
        ]);

        $conversion = CampaignConversion::create($validated);

        return redirect()
            ->route('frontend.campaign-conversions.show', $conversion)
            ->with('success', __('campaign_conversions.messages.created_successfully'));
    }

    public function edit(CampaignConversion $campaignConversion): View
    {
        $campaigns = Campaign::where('status', 'active')->get();
        $conversionTypes = [
            'purchase' => __('campaign_conversions.conversion_types.purchase'),
            'signup' => __('campaign_conversions.conversion_types.signup'),
            'download' => __('campaign_conversions.conversion_types.download'),
            'lead' => __('campaign_conversions.conversion_types.lead'),
            'subscription' => __('campaign_conversions.conversion_types.subscription'),
            'trial' => __('campaign_conversions.conversion_types.trial'),
            'custom' => __('campaign_conversions.conversion_types.custom'),
        ];

        $statuses = [
            'pending' => __('campaign_conversions.statuses.pending'),
            'completed' => __('campaign_conversions.statuses.completed'),
            'cancelled' => __('campaign_conversions.statuses.cancelled'),
            'refunded' => __('campaign_conversions.statuses.refunded'),
        ];

        $deviceTypes = [
            'mobile' => __('campaign_conversions.device_types.mobile'),
            'tablet' => __('campaign_conversions.device_types.tablet'),
            'desktop' => __('campaign_conversions.device_types.desktop'),
        ];

        return view('campaign-conversions.edit', compact(
            'campaignConversion',
            'campaigns',
            'conversionTypes',
            'statuses',
            'deviceTypes'
        ));
    }

    public function update(Request $request, CampaignConversion $campaignConversion): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:discount_campaigns,id',
            'conversion_type' => 'required|string|max:255',
            'conversion_value' => 'required|numeric|min:0',
            'status' => 'required|string|max:255',
            'converted_at' => 'required|date',
            'source' => 'nullable|string|max:255',
            'medium' => 'nullable|string|max:255',
            'device_type' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'custom_attributes' => 'nullable|array',
        ]);

        $campaignConversion->update($validated);

        return redirect()
            ->route('frontend.campaign-conversions.show', $campaignConversion)
            ->with('success', __('campaign_conversions.messages.updated_successfully'));
    }

    public function destroy(CampaignConversion $campaignConversion): \Illuminate\Http\RedirectResponse
    {
        $campaignConversion->delete();

        return redirect()
            ->route('frontend.campaign-conversions.index')
            ->with('success', __('campaign_conversions.messages.deleted_successfully'));
    }
}
