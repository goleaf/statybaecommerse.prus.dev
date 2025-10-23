<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Menu;
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
    public function __construct(private readonly MenuRepository $menus)
    {
    }

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $location = $request->get('location');
        $menus = $this->menus->getActiveMenus($location)->filter(function (Menu $menu) {
            return ! empty($menu->name) && ! empty($menu->key) && $menu->allItems->isNotEmpty();
        })->values();

        return response()->json([
            'success' => true,
            'data' => $menus->map(fn (array $menu): array => $this->transformMenu($menu))->all(),
        ]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(string $key): JsonResponse
    {
        $menu = $this->menus->findActiveMenuByKey($key);
        if (! $menu) {
            return response()->json(['success' => false, 'message' => __('api.menu_not_found')], 404);
        }

        return response()->json(['success' => true, 'data' => $this->transformMenu($menu)]);
    }

    /**
     * Handle byLocation functionality with proper error handling.
     */
    public function byLocation(string $location): JsonResponse
    {
        $menu = $this->menus->findActiveMenuByLocation($location);
        if (! $menu) {
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
            'id' => $menu['id'],
            'key' => $menu['key'],
            'name' => $menu['name'],
            'location' => $menu['location'],
            'items' => $menu['items'],
        ];
    }
}
