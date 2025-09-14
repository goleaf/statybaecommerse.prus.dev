<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final /**
 * SystemSettingController
 * 
 * HTTP controller handling web requests and responses.
 */
class SystemSettingController extends Controller
{
    public function index(Request $request): View
    {
        $categories = SystemSettingCategory::active()
            ->with(['settings' => function ($query) {
                $query->active()->public()->ordered();
            }])
            ->ordered()
            ->get();

        $settings = SystemSetting::active()
            ->public()
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            })
            ->when($request->filled('group'), function ($query) use ($request) {
                $query->where('group', $request->group);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->searchable($request->search);
            })
            ->ordered()
            ->get()
            ->skipWhile(function ($setting) {
                // Skip system settings that are not properly configured for display
                return empty($setting->key) || 
                       !$setting->is_active ||
                       !$setting->is_public ||
                       empty($setting->group) ||
                       empty($setting->name);
            })
            ->paginate(20);

        return view('system-settings.index', compact('categories', 'settings'));
    }

    public function show(string $key): View
    {
        $setting = SystemSetting::where('key', $key)
            ->active()
            ->public()
            ->firstOrFail();

        // Update access count and last accessed time
        $setting->increment('access_count');
        $setting->update(['last_accessed_at' => now()]);

        $relatedSettings = SystemSetting::active()
            ->public()
            ->where('group', $setting->group)
            ->where('id', '!=', $setting->id)
            ->limit(5)
            ->get()
            ->skipWhile(function ($relatedSetting) {
                // Skip related system settings that are not properly configured for display
                return empty($relatedSetting->key) || 
                       !$relatedSetting->is_active ||
                       !$relatedSetting->is_public ||
                       empty($relatedSetting->group) ||
                       empty($relatedSetting->name);
            });

        return view('system-settings.show', compact('setting', 'relatedSettings'));
    }

    public function category(string $slug): View
    {
        $category = SystemSettingCategory::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $settings = $category->settings()
            ->active()
            ->public()
            ->ordered()
            ->get()
            ->skipWhile(function ($setting) {
                // Skip system settings that are not properly configured for display
                return empty($setting->key) || 
                       !$setting->is_active ||
                       !$setting->is_public ||
                       empty($setting->group) ||
                       empty($setting->name);
            })
            ->paginate(20);

        $relatedCategories = SystemSettingCategory::active()
            ->where('id', '!=', $category->id)
            ->limit(5)
            ->get();

        return view('system-settings.category', compact('category', 'settings', 'relatedCategories'));
    }

    public function group(string $group): View
    {
        $settings = SystemSetting::active()
            ->public()
            ->where('group', $group)
            ->ordered()
            ->get()
            ->skipWhile(function ($setting) {
                // Skip system settings that are not properly configured for display
                return empty($setting->key) || 
                       !$setting->is_active ||
                       !$setting->is_public ||
                       empty($setting->group) ||
                       empty($setting->name);
            })
            ->paginate(20);

        $categories = SystemSettingCategory::active()
            ->withCount(['settings' => function ($query) use ($group) {
                $query->where('group', $group)->active()->public();
            }])
            ->having('settings_count', '>', 0)
            ->get();

        return view('system-settings.group', compact('settings', 'categories', 'group'));
    }

    public function search(Request $request): View
    {
        $query = $request->get('q', '');

        $settings = SystemSetting::active()
            ->public()
            ->searchable($query)
            ->ordered()
            ->paginate(20);

        $categories = SystemSettingCategory::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->get();

        return view('system-settings.search', compact('settings', 'categories', 'query'));
    }

    public function api(Request $request): JsonResponse
    {
        $settings = SystemSetting::active()
            ->public()
            ->when($request->filled('group'), function ($query) use ($request) {
                $query->where('group', $request->group);
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            })
            ->when($request->filled('keys'), function ($query) use ($request) {
                $keys = explode(',', $request->keys);
                $query->whereIn('key', $keys);
            })
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            });

        return response()->json($settings);
    }

    public function apiByKey(string $key): JsonResponse
    {
        $setting = SystemSetting::where('key', $key)
            ->active()
            ->public()
            ->first();

        if (! $setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        // Update access count
        $setting->increment('access_count');
        $setting->update(['last_accessed_at' => now()]);

        return response()->json([
            'key' => $setting->key,
            'name' => $setting->getTranslatedName(),
            'value' => $setting->value,
            'type' => $setting->type,
            'group' => $setting->group,
            'description' => $setting->getTranslatedDescription(),
            'help_text' => $setting->getTranslatedHelpText(),
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = SystemSettingCategory::active()
            ->withCount(['settings' => function ($query) {
                $query->active()->public();
            }])
            ->having('settings_count', '>', 0)
            ->ordered()
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->getTranslatedName(),
                    'slug' => $category->slug,
                    'description' => $category->getTranslatedDescription(),
                    'icon' => $category->getIconClass(),
                    'color' => $category->color,
                    'settings_count' => $category->settings_count,
                ];
            });

        return response()->json($categories);
    }

    public function groups(): JsonResponse
    {
        $groups = SystemSetting::active()
            ->public()
            ->select('group')
            ->selectRaw('count(*) as settings_count')
            ->groupBy('group')
            ->orderBy('settings_count', 'desc')
            ->get()
            ->map(function ($group) {
                return [
                    'name' => $group->group,
                    'label' => ucfirst($group->group),
                    'settings_count' => $group->settings_count,
                ];
            });

        return response()->json($groups);
    }
}
