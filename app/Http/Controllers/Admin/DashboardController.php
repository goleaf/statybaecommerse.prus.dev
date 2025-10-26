<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard screen for the currently authenticated administrator.
     *
     * Keeping the request instance available allows us to expand the handler with
     * per-user dashboard configuration (widgets, filters, etc.) without altering
     * the signature later on.
     */
    public function index(Request $request): View
    {
        // Render the main admin dashboard view that aggregates system metrics.
        return view('admin.dashboard');
    }
}
