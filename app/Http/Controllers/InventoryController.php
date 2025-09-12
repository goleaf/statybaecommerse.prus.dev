<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['brand', 'categories'])
            ->where('is_visible', true)
            ->published();

        // Apply filters
        if ($request->filled('stock_status')) {
            $query->where(function ($q) use ($request) {
                match ($request->stock_status) {
                    'in_stock' => $q->where('manage_stock', true)
                        ->whereRaw('stock_quantity > low_stock_threshold'),
                    'low_stock' => $q->where('manage_stock', true)
                        ->where('stock_quantity', '>', 0)
                        ->whereRaw('stock_quantity <= low_stock_threshold'),
                    'out_of_stock' => $q->where('manage_stock', true)
                        ->where('stock_quantity', '<=', 0),
                    'not_tracked' => $q->where('manage_stock', false),
                    default => null,
                };
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'sku', 'price', 'stock_quantity', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }

        $products = $query->paginate(20)->withQueryString();

        return view('inventory', compact('products'));
    }

    public function show(Product $product): View
    {
        $product->load(['brand', 'categories', 'reviews', 'variants']);
        
        return view('products.show', compact('product'));
    }
}
