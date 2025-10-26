<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Support\Contracts\Entities\BrandContract;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class BrandController extends Controller
{
    use HandlesContentNegotiation;

    public function index(Request $request): JsonResponse|View|Response
    {
        // Clamp the requested page size so the API cannot request too many or too few records.
        $perPage = max(1, min((int) $request->get('per_page', 20), 100));
        $search = $request->string('search')->toString();

        $brands = Brand::query()
            ->visible()
            ->withCount('products')
            ->when($search !== '', function (Builder $query) use ($search): void {
                // Apply a simple LIKE filter whenever a non-empty search term is provided.
                $query->where('name', 'like', "%{$search}%");
            })
            // Lean on the shared OrdersByName scope so API payloads stay alphabetically deterministic.
            ->orderedByName()
            ->paginate($perPage);

        $payload = BrandContract::forCollection($brands, [
            'search' => $search !== '' ? $search : null,
        ]);

        return $this->respondWithContract($request, $payload);
    }

    public function show(Request $request, Brand $brand): JsonResponse|View|Response
    {
        // Ensure the related products are available without issuing duplicate queries downstream.
        $brand->loadMissing('products');

        $payload = BrandContract::forBrand($brand);

        return $this->respondWithContract($request, $payload);
    }
}
