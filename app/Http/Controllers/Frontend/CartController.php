<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class CartController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Implement cart listing
        return response()->json(['message' => 'Cart listing not implemented yet']);
    }

    public function add(Request $request)
    {
        // TODO: Implement add to cart
        return response()->json(['message' => 'Add to cart not implemented yet']);
    }

    public function update(Request $request, string $id)
    {
        // TODO: Implement cart item update
        return response()->json(['message' => 'Cart update not implemented yet', 'id' => $id]);
    }

    public function remove(string $id)
    {
        // TODO: Implement cart item removal
        return response()->json(['message' => 'Cart item removal not implemented yet', 'id' => $id]);
    }
}
