<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Support\Cache\CacheKeys;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $locale = app()->getLocale();

        $stats = Cache::remember(CacheKeys::homeStats($locale), CacheKeys::TTL_MINUTE, static function (): array {
            return [
                'products_count' => Product::query()
                    ->where('is_visible', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->count(),
                'categories_count' => Category::query()
                    ->where('is_visible', true)
                    ->count(),
                'brands_count' => Brand::query()
                    ->where('is_enabled', true)
                    ->count(),
                'reviews_count' => Review::query()
                    ->where('is_approved', true)
                    ->count(),
                'avg_rating' => (float) (Review::query()
                    ->where('is_approved', true)
                    ->avg('rating') ?? 0),
            ];
        });

        return view('frontend.home.index', [
            'stats' => $stats,
        ]);
    }
}
