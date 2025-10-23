<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $search = Str::of((string) $request->input('search'))->trim()->whenEmpty(fn () => null)->toString();

        $brandsQuery = Brand::query()
            ->withCount(['products as visible_products_count' => fn ($query) => $query->where('is_visible', true)]);

        if ($search) {
            $brandsQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            });
        }

        $sort = $request->input('sort', 'name_asc');

        if ($sort === 'name_desc') {
            $brandsQuery->orderByDesc('name');
        } else {
            $brandsQuery->orderBy('name');
        }

        /** @var LengthAwarePaginator $brands */
        $brands = $brandsQuery->paginate(16)->withQueryString();

        return view('brands.index', [
            'brands' => $brands,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function show(Brand $brand): View
    {
        $brand->load(['media']);

        $products = $brand->products()
            ->with(['media', 'brand'])
            ->where('is_visible', true)
            ->latest()
            ->take(12)
            ->get();

        $seoTitle = $brand->seo_title ?: $brand->name;
        $seoDescription = $brand->seo_description ?: ($brand->description ? Str::limit($brand->description, 155) : $brand->name);

        return view('brands.show', [
            'brand' => $brand,
            'products' => $products,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
        ]);
    }
}
