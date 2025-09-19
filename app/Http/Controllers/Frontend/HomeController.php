<?php declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class HomeController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Implement homepage
        return response()->json(['message' => 'Homepage not implemented yet']);
    }
}

