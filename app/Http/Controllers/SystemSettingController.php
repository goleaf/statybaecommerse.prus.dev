<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenApi\Attributes as OA;

/**
 * SystemSettingController
 *
 * HTTP controller handling SystemSettingController related web requests, responses, and business logic with proper validation and error handling.
 */
#[OA\Tag(name: 'System Settings', description: 'Public system configuration lookup endpoints.')]
final class SystemSettingController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        $categories = SystemSettingCategory::active()->with(['settings' => function ($query): void {
            $query->active()->public()->ordered();
        }])->ordered()->get();
        $settings = SystemSetting::active()->public()->when($request->filled('category'), function ($query) use ($request): void {
            $query->whereHas('category', function ($q) use ($request): void {
                $q->where('slug', $request->category);
            });
        })->when($request->filled('group'), function ($query) use ($request): void {
            $query->where('group', $request->group);
        })->when($request->filled('search'), function ($query) use ($request): void {
            $query->searchable($request->search);
        })->whereNotNull('key')
            // Ensure the pagination query excludes settings missing a key
            ->where('key', '!=', '')
            // Ensure the pagination query excludes settings missing a group
            ->whereNotNull('group')
            ->where('group', '!=', '')
            // Ensure the pagination query excludes settings missing a name
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->ordered()
            ->paginate(20);

        return view('system-settings.index', compact('categories', 'settings'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(string $key): View
    {
        $setting = SystemSetting::where('key', $key)->active()->public()->firstOrFail();
        // Update access count and last accessed time
        $setting->increment('access_count');
        $setting->update(['last_accessed_at' => now()]);
        $relatedSettings = SystemSetting::active()->public()->where('group', $setting->group)->where('id', '!=', $setting->id)->limit(5)->get()->skipWhile(function ($relatedSetting) {
            // Skip related system settings that are not properly configured for display
            return empty($relatedSetting->key) || ! $relatedSetting->is_active || ! $relatedSetting->is_public || empty($relatedSetting->group) || empty($relatedSetting->name);
        });

        return view('system-settings.show', compact('setting', 'relatedSettings'));
    }

    /**
     * Handle category functionality with proper error handling.
     */
    public function category(string $slug): View
    {
        $category = SystemSettingCategory::where('slug', $slug)->active()->firstOrFail();
        $settings = $category->settings()->active()->public()
            ->whereNotNull('key')
            // Ensure the pagination query excludes settings missing a key
            ->where('key', '!=', '')
            // Ensure the pagination query excludes settings missing a group
            ->whereNotNull('group')
            ->where('group', '!=', '')
            // Ensure the pagination query excludes settings missing a name
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->ordered()
            ->paginate(20);
        $relatedCategories = SystemSettingCategory::active()->where('id', '!=', $category->id)->limit(5)->get();

        return view('system-settings.category', compact('category', 'settings', 'relatedCategories'));
    }

    /**
     * Handle group functionality with proper error handling.
     */
    public function group(string $group): View
    {
        $settings = SystemSetting::active()->public()->where('group', $group)
            ->whereNotNull('key')
            // Ensure the pagination query excludes settings missing a key
            ->where('key', '!=', '')
            // Ensure the pagination query excludes settings missing a name
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->ordered()
            ->paginate(20);
        $categories = SystemSettingCategory::active()->withCount(['settings' => function ($query) use ($group): void {
            $query->where('group', $group)->active()->public();
        }])->having('settings_count', '>', 0)->get();

        return view('system-settings.group', compact('settings', 'categories', 'group'));
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(Request $request): View
    {
        $query = $request->get('q', '');
        $settings = SystemSetting::active()->public()->searchable($query)->ordered()->paginate(20);
        $categories = SystemSettingCategory::active()->where(function ($q) use ($query): void {
            $q->where('name', 'like', "%{$query}%")->orWhere('description', 'like', "%{$query}%");
        })->get();

        return view('system-settings.search', compact('settings', 'categories', 'query'));
    }

    #[OA\Get(
        path: '/system-settings',
        summary: 'List public system settings',
        description: 'Return key-value pairs for public system settings. Results can be filtered by group, category, or key whitelist.',
        tags: ['System Settings'],
        parameters: [
            new OA\QueryParameter(name: 'group', in: 'query', description: 'Filter settings by group slug.', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'category', in: 'query', description: 'Filter by category slug.', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'keys', in: 'query', description: 'Comma separated list of keys to include.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Key-value map of public system settings.',
                content: new OA\JsonContent(ref: '#/components/schemas/SystemSettingValueMap')
            ),
        ]
    )]
    /**
     * Handle api functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/system-settings',
        summary: 'List public system settings.',
        tags: ['System Settings'],
        parameters: [
            new OA\QueryParameter(
                name: 'group',
                description: 'Filter settings by group.',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\QueryParameter(
                name: 'category',
                description: 'Filter settings by category slug.',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\QueryParameter(
                name: 'keys',
                description: 'Comma separated list of setting keys to include.',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/SystemSettingsIndex'),
        ]
    )]
    public function api(Request $request): JsonResponse
    {
        $settings = SystemSetting::active()->public()->when($request->filled('group'), function ($query) use ($request): void {
            $query->where('group', $request->group);
        })->when($request->filled('category'), function ($query) use ($request): void {
            $query->whereHas('category', function ($q) use ($request): void {
                $q->where('slug', $request->category);
            });
        })->when($request->filled('keys'), function ($query) use ($request): void {
            $keys = explode(',', $request->keys);
            $query->whereIn('key', $keys);
        })->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });

        return response()->json($settings);
    }

    #[OA\Get(
        path: '/system-settings/{key}',
        summary: 'Retrieve a single system setting',
        tags: ['System Settings'],
        parameters: [
            new OA\Parameter(name: 'key', in: 'path', required: true, description: 'System setting key.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Public system setting payload.',
                content: new OA\JsonContent(ref: '#/components/schemas/SystemSettingEntry')
            ),
            new OA\Response(
                response: 404,
                description: 'Setting not found.',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
        ]
    )]
    /**
     * Handle apiByKey functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/system-settings/{key}',
        summary: 'Retrieve a single system setting by key.',
        tags: ['System Settings'],
        parameters: [
            new OA\PathParameter(
                name: 'key',
                description: 'Unique setting key.',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/SystemSettingItem'),
            new OA\Response(response: 404, ref: '#/components/responses/SystemSettingNotFound'),
        ]
    )]
    public function apiByKey(string $key): JsonResponse
    {
        $setting = SystemSetting::where('key', $key)->active()->public()->first();
        if (! $setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }
        // Update access count
        $setting->increment('access_count');
        $setting->update(['last_accessed_at' => now()]);

        return response()->json(['key' => $setting->key, 'name' => $setting->getTranslatedName(), 'value' => $setting->value, 'type' => $setting->type, 'group' => $setting->group, 'description' => $setting->getTranslatedDescription(), 'help_text' => $setting->getTranslatedHelpText()]);
    }

    #[OA\Get(
        path: '/system-settings/categories',
        summary: 'List system setting categories',
        tags: ['System Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Active system setting categories.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/SystemSettingCategory'))
            ),
        ]
    )]
    /**
     * Handle categories functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/system-settings/categories',
        summary: 'List public system setting categories.',
        tags: ['System Settings'],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/SystemSettingCategories'),
        ]
    )]
    public function categories(): JsonResponse
    {
        $categories = SystemSettingCategory::active()->withCount(['settings' => function ($query): void {
            $query->active()->public();
        }])->having('settings_count', '>', 0)->ordered()->get()->map(function ($category) {
            return ['id' => $category->id, 'name' => $category->getTranslatedName(), 'slug' => $category->slug, 'description' => $category->getTranslatedDescription(), 'icon' => $category->getIconClass(), 'color' => $category->color, 'settings_count' => $category->settings_count];
        });

        return response()->json($categories);
    }

    #[OA\Get(
        path: '/system-settings/groups',
        summary: 'List system setting groups',
        tags: ['System Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Active groups with counts.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/SystemSettingGroup'))
            ),
        ]
    )]
    /**
     * Handle groups functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/system-settings/groups',
        summary: 'List public system setting groups.',
        tags: ['System Settings'],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/SystemSettingGroups'),
        ]
    )]
    public function groups(): JsonResponse
    {
        $groups = SystemSetting::active()->public()->select('group')->selectRaw('count(*) as settings_count')->groupBy('group')->orderBy('settings_count', 'desc')->get()->map(function ($group) {
            return ['name' => $group->group, 'label' => ucfirst($group->group), 'settings_count' => $group->settings_count];
        });

        return response()->json($groups);
    }
}
