<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Location::enabled()
            ->with(['country'])
            ->orderBy('sort_order')
            ->orderBy('name');

        // Filter by type
        if ($request->filled('type')) {
            $query->byType($request->get('type'));
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->get('city') . '%');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('address_line_1', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $locations = $query->paginate(12);

        // Get filter options
        $types = Location::enabled()
            ->distinct()
            ->pluck('type')
            ->mapWithKeys(fn ($type) => [
                $type => match ($type) {
                    'warehouse' => __('locations.warehouse'),
                    'store' => __('locations.store'),
                    'office' => __('locations.office'),
                    'pickup_point' => __('locations.pickup_point'),
                    'other' => __('locations.other'),
                    default => $type,
                }
            ]);

        $cities = Location::enabled()
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('locations.index', compact('locations', 'types', 'cities'));
    }

    public function show(Location $location): View
    {
        if (!$location->is_enabled) {
            abort(404);
        }

        $location->load(['country']);

        // Get related locations (same type or same city)
        $relatedLocations = Location::enabled()
            ->where('id', '!=', $location->id)
            ->where(function ($query) use ($location) {
                $query->where('type', $location->type)
                      ->orWhere('city', $location->city);
            })
            ->limit(4)
            ->get();

        return view('locations.show', compact('location', 'relatedLocations'));
    }

    public function contact(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        // Here you would typically send an email or store the message
        // For now, we'll just return a success response

        return back()->with('success', __('locations.contact_success'));
    }
}