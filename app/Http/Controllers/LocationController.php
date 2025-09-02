<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Contracts\View\View;

class LocationController extends Controller
{
    public function index(string $locale): View
    {
        $locations = Inventory::query()->orderByDesc('is_default')->orderBy('name')->paginate(20);

        return view('locations.index', compact('locations'));
    }

    public function show(string $locale, string $id): View
    {
        $locationId = (int) $id;
        $location = Inventory::query()->findOrFail($locationId);

        return view('locations.show', compact('location'));
    }
}
