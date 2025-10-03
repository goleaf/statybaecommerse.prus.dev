<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProductController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Implement product listing
        return response()->json(['message' => 'Product listing not implemented yet']);
    }

    public function show(Product $product): View
    {
        $product->loadMissing([
            'brand',
            'categories',
            'media',
            'reviews',
            'attributes.values',
            'variants' => static function ($query): void {
                $query->with([
                    'images',
                    'attributes.attribute',
                ]);
            },
        ]);

        return view('products.show', [
            'product' => $product,
        ]);
    }
}
