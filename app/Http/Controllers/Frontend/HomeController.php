<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\HomepageDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class HomeController extends Controller
{
    public function __construct(private readonly HomepageDataProvider $homepageData)
    {
    }

    public function index(Request $request): View
    {
        return view('frontend.home.index', [
            'stats' => $this->homepageData->stats(),
            'featuredProducts' => $this->homepageData->featuredProducts(),
            'newArrivals' => $this->homepageData->newArrivals(),
            'trendingProducts' => $this->homepageData->trendingProducts(),
            'topCategories' => $this->homepageData->topCategories(),
            'topBrands' => $this->homepageData->topBrands(),
        ]);
    }
}
