<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\MenuRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * MenuController
 *
 * HTTP controller handling MenuController related web requests, responses, and business logic with proper validation and error handling.
 */
final class MenuController extends Controller
{
    public function __construct(
        private readonly MenuRepository $menuRepository,
    ) {}

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $location = $request->get('location');
        $menus = $this->menuRepository->all($location, app()->getLocale())
            ->filter(static fn (array $menu): bool => ! empty($menu['items']))
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $menus->map(fn (array $menu): array => $this->transformMenu($menu))->all(),
        ]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(string $key): JsonResponse
    {
        $menu = $this->menuRepository->byKey($key, app()->getLocale());

        if ($menu === null || empty($menu['items'])) {
            return response()->json(['success' => false, 'message' => __('api.menu_not_found')], 404);
        }

        return response()->json(['success' => true, 'data' => $this->transformMenu($menu)]);
    }

    /**
     * Handle byLocation functionality with proper error handling.
     */
    public function byLocation(string $location): JsonResponse
    {
        $menu = $this->menuRepository->byLocation($location, app()->getLocale());

        if ($menu === null || empty($menu['items'])) {
            return response()->json(['success' => false, 'message' => __('api.menu_not_found_for_location')], 404);
        }

        return response()->json(['success' => true, 'data' => $this->transformMenu($menu)]);
    }

    /**
     * Normalize menu payload for API responses.
     */
    private function transformMenu(array $menu): array
    {
        return [
            'id'       => $menu['id'],
            'key'      => $menu['key'],
            'name'     => $menu['name'],
            'location' => $menu['location'],
            'items'    => $menu['items'],
        ];
    }
}
