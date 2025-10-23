<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProductController extends Controller
{
    public function __construct(private readonly ProductCatalogueDataProvider $dataProvider) {}

    public function index(Request $request): View
    {
        $listing = $this->dataProvider->listing($request->all());

        return view('frontend.products.index', $listing);
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
