<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Support\Contracts\Entities\BrandContract;
use Illuminate\Http\JsonResponse;

final class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = Brand::query()->where('is_enabled', true)->with('media')->orderBy('name')->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'brands' => $brands->map(static fn (Brand $brand): array => BrandContract::fromModel($brand))->toArray(),
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function show(Brand $brand): JsonResponse
    {
        $brand->load('media');

        return response()->json([
            'success' => true,
            'data' => [
                'brand' => BrandContract::fromModel($brand),
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }
}
