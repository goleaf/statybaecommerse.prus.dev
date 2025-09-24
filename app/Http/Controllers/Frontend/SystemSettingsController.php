<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SystemSettingsController
 *
 * HTTP controller handling SystemSettingsController related web requests, responses, and business logic with proper validation and error handling.
 */
final class SystemSettingsController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        $query = SystemSetting::query()->with('category')->where('is_active', true);
        // Apply filters
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')->orWhere('key', 'like', '%'.$request->search.'%')->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }
        // Only show public settings
        $query->where('is_public', true);
        $settings = $query->ordered()->paginate(20);
        $categories = SystemSettingCategory::active()->ordered()->get();
        $groups = SystemSetting::select('group')->where('is_active', true)->where('is_public', true)->distinct()->orderBy('group')->pluck('group');
        $types = SystemSetting::select('type')->where('is_active', true)->where('is_public', true)->distinct()->orderBy('type')->pluck('type');

        return view('system-settings.index', compact('settings', 'categories', 'groups', 'types'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(SystemSetting $setting): View
    {
        // Ensure the setting is public and active
        if (! $setting->is_public || ! $setting->is_active) {
            abort(404);
        }
        $setting->load('category');

        return view('system-settings.show', compact('setting'));
    }

    /**
     * Handle byCategory functionality with proper error handling.
     */
    public function byCategory(SystemSettingCategory $category): View
    {
        if (! $category->is_active) {
            abort(404);
        }
        $settings = $category->settings()->where('is_active', true)->where('is_public', true)->ordered()->get();

        return view('system-settings.category', compact('category', 'settings'));
    }

    /**
     * Handle byGroup functionality with proper error handling.
     */
    public function byGroup(string $group): View
    {
        $settings = SystemSetting::where('group', $group)->where('is_active', true)->where('is_public', true)->with('category')->ordered()->get();
        if ($settings->isEmpty()) {
            abort(404);
        }

        return view('system-settings.group', compact('group', 'settings'));
    }

    /**
     * Handle api functionality with proper error handling.
     */
    public function api(Request $request): JsonResponse
    {
        $query = SystemSetting::query()->where('is_active', true)->where('is_public', true);
        // Apply filters
        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        if ($request->filled('key')) {
            $query->where('key', $request->key);
        }
        $settings = $query->get()->map(function ($setting) {
            return $setting->getApiResponse();
        });

        return response()->json(['success' => true, 'data' => $settings, 'meta' => ['total' => $settings->count(), 'timestamp' => now()->toISOString()]]);
    }

    /**
     * Handle getValue functionality with proper error handling.
     */
    public function getValue(string $key): JsonResponse
    {
        $setting = SystemSetting::where('key', $key)->where('is_active', true)->where('is_public', true)->first();
        if (! $setting) {
            return response()->json(['success' => false, 'message' => __('admin.system_settings.setting_not_found')], 404);
        }

        return response()->json(['success' => true, 'data' => ['key' => $setting->key, 'value' => $setting->value, 'type' => $setting->type, 'updated_at' => $setting->updated_at]]);
    }

    /**
     * Handle categories functionality with proper error handling.
     */
    public function categories(): JsonResponse
    {
        $categories = SystemSettingCategory::active()->ordered()->get()->map(function ($category) {
            return ['id' => $category->id, 'name' => $category->getTranslatedName(), 'slug' => $category->slug, 'description' => $category->getTranslatedDescription(), 'icon' => $category->getIconClass(), 'color' => $category->color, 'settings_count' => $category->getActiveSettingsCount()];
        });

        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * Handle groups functionality with proper error handling.
     */
    public function groups(): JsonResponse
    {
        $groups = SystemSetting::select('group')->where('is_active', true)->where('is_public', true)->distinct()->orderBy('group')->get()->map(function ($setting) {
            $count = SystemSetting::where('group', $setting->group)->where('is_active', true)->where('is_public', true)->count();

            return ['name' => $setting->group, 'label' => __('admin.system_settings.'.$setting->group), 'count' => $count];
        });

        return response()->json(['success' => true, 'data' => $groups]);
    }
}
