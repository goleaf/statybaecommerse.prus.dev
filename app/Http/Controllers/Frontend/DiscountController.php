<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class DiscountController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Implement discount listing
        return response()->json(['message' => 'Discount listing not implemented yet']);
    }

    public function show(string $id)
    {
        // TODO: Implement discount details
        return response()->json(['message' => 'Discount details not implemented yet', 'id' => $id]);
    }

    public function validate(Request $request)
    {
        // TODO: Implement discount validation
        return response()->json(['message' => 'Discount validation not implemented yet']);
    }
}
