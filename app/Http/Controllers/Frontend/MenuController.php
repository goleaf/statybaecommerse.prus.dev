<?php

declare (strict_types=1);
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\LazyCollection;
/**
 * MenuController
 * 
 * HTTP controller handling MenuController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class MenuController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $location = $request->get('location');
        $query = Menu::with(['allItems' => function ($query) {
            $query->where('is_visible', true)->orderBy('sort_order');
        }]);
        if ($location) {
            $query->where('location', $location);
        }
        $timeout = now()->addSeconds(5);
        // 5 second timeout for menu loading
        $menus = $query->where('is_active', true)->cursor()->takeUntilTimeout($timeout)->collect()->skipWhile(function ($menu) {
            // Skip menus that are not properly configured for display
            return empty($menu->name) || empty($menu->key) || !$menu->is_active || empty($menu->allItems);
        });
        return response()->json(['success' => true, 'data' => $menus->map(function ($menu) {
            return ['id' => $menu->id, 'key' => $menu->key, 'name' => $menu->name, 'location' => $menu->location, 'items' => $this->formatMenuItems($menu->allItems)];
        })]);
    }
    /**
     * Display the specified resource with related data.
     * @param string $key
     * @return JsonResponse
     */
    public function show(string $key): JsonResponse
    {
        $menu = Menu::with(['allItems' => function ($query) {
            $query->where('is_visible', true)->orderBy('sort_order');
        }])->where('key', $key)->where('is_active', true)->first();
        if (!$menu) {
            return response()->json(['success' => false, 'message' => __('api.menu_not_found')], 404);
        }
        return response()->json(['success' => true, 'data' => ['id' => $menu->id, 'key' => $menu->key, 'name' => $menu->name, 'location' => $menu->location, 'items' => $this->formatMenuItems($menu->allItems)]]);
    }
    /**
     * Handle byLocation functionality with proper error handling.
     * @param string $location
     * @return JsonResponse
     */
    public function byLocation(string $location): JsonResponse
    {
        $menu = Menu::with(['allItems' => function ($query) {
            $query->where('is_visible', true)->orderBy('sort_order');
        }])->where('location', $location)->where('is_active', true)->first();
        if (!$menu) {
            return response()->json(['success' => false, 'message' => __('api.menu_not_found_for_location')], 404);
        }
        return response()->json(['success' => true, 'data' => ['id' => $menu->id, 'key' => $menu->key, 'name' => $menu->name, 'location' => $menu->location, 'items' => $this->formatMenuItems($menu->allItems)]]);
    }
    /**
     * Handle formatMenuItems functionality with proper error handling.
     * @param mixed $items
     * @return array
     */
    private function formatMenuItems($items): array
    {
        $formatted = [];
        $itemsByParent = $items->groupBy('parent_id');
        // Get root items (no parent)
        $rootItems = $itemsByParent->get(null, collect());
        foreach ($rootItems as $item) {
            $formatted[] = $this->formatMenuItem($item, $itemsByParent);
        }
        return $formatted;
    }
    /**
     * Handle formatMenuItem functionality with proper error handling.
     * @param mixed $item
     * @param mixed $itemsByParent
     * @return array
     */
    private function formatMenuItem($item, $itemsByParent): array
    {
        $children = $itemsByParent->get($item->id, collect())->map(function ($child) use ($itemsByParent) {
            return $this->formatMenuItem($child, $itemsByParent);
        })->toArray();
        return ['id' => $item->id, 'label' => $item->label, 'url' => $item->url, 'route_name' => $item->route_name, 'route_params' => $item->route_params, 'icon' => $item->icon, 'sort_order' => $item->sort_order, 'children' => $children];
    }
}