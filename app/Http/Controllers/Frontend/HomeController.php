<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

use function now;

final class HomeController extends Controller
{
    public function index(): View
    {
        $products = $this->getFeaturedProducts();

        return view('shop.index', [
            'products' => $products,
        ]);
    }

    private function getFeaturedProducts(): Collection
    {
        return Product::query()
            ->withoutGlobalScopes()
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['media', 'prices.currency'])
            ->latest('published_at')
            ->limit(12)
            ->get();
    }
}
