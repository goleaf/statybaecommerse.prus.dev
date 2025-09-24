<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class ProfileController extends Controller
{
    public function show(Request $request)
    {
        // TODO: Implement profile display
        return response()->json(['message' => 'Profile not implemented yet']);
    }

    public function edit(Request $request)
    {
        // TODO: Implement profile edit form
        return response()->json(['message' => 'Profile edit not implemented yet']);
    }

    public function update(Request $request)
    {
        // TODO: Implement profile update
        return response()->json(['message' => 'Profile update not implemented yet']);
    }
}
