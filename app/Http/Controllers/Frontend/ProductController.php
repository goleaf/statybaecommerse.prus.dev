<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class ProductController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Implement product listing
        return response()->json(['message' => 'Product listing not implemented yet']);
    }

    public function show(string $id)
    {
        // TODO: Implement product details
        return response()->json(['message' => 'Product details not implemented yet', 'id' => $id]);
    }
}
