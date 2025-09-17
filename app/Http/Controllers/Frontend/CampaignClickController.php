<?php

declare (strict_types=1);
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CampaignClick;
use Illuminate\Http\Request;
use Illuminate\View\View;
/**
 * CampaignClickController
 * 
 * HTTP controller handling CampaignClickController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class CampaignClickController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
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
        if ($request->has('date_from')) {
            $query->where('clicked_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('clicked_at', '<=', $request->date_to);
        }
        // For authenticated users, show only their clicks
        if (auth()->check()) {
            $query->where('customer_id', auth()->id());
        }
        $clicks = $query->orderBy('clicked_at', 'desc')->paginate(15);
        return view('campaign-clicks.index', compact('clicks'));
    }
    /**
     * Display the specified resource with related data.
     * @param CampaignClick $campaignClick
     * @return View
     */
    public function show(CampaignClick $campaignClick): View
    {
        // Check if user can view this click
        if (auth()->check() && $campaignClick->customer_id !== auth()->id()) {
            abort(403, __('campaign_clicks.unauthorized'));
        }
        $campaignClick->load(['campaign', 'customer', 'conversions']);
        return view('campaign-clicks.show', compact('campaignClick'));
    }
}