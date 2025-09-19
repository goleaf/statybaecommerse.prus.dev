<?php declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class BrandController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Implement brand listing
        return response()->json(['message' => 'Brand listing not implemented yet']);
    }

    public function show(string $id)
    {
        // TODO: Implement brand details
        return response()->json(['message' => 'Brand details not implemented yet', 'id' => $id]);
    }
}

