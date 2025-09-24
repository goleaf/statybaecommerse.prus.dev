<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Implement category listing
        return response()->json(['message' => 'Category listing not implemented yet']);
    }

    public function show(string $id)
    {
        // TODO: Implement category details
        return response()->json(['message' => 'Category details not implemented yet', 'id' => $id]);
    }
}
