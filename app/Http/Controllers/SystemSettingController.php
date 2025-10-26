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
        })->ordered()->get()->skipWhile(fn ($setting): bool => // Skip system settings that are not properly configured for display
            empty($setting->key) || ! $setting->is_active || ! $setting->is_public || empty($setting->group) || empty($setting->name))->paginate(20);

        return view('system-settings.index', ['categories' => $categories, 'settings' => $settings]);
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
        $relatedSettings = SystemSetting::active()->public()->where('group', $setting->group)->where('id', '!=', $setting->id)->limit(5)->get()->skipWhile(fn ($relatedSetting): bool => // Skip related system settings that are not properly configured for display
            empty($relatedSetting->key) || ! $relatedSetting->is_active || ! $relatedSetting->is_public || empty($relatedSetting->group) || empty($relatedSetting->name));

        return view('system-settings.show', ['setting' => $setting, 'relatedSettings' => $relatedSettings]);
    }

    /**
     * Handle category functionality with proper error handling.
     */
    public function category(string $slug): View
    {
        $category = SystemSettingCategory::where('slug', $slug)->active()->firstOrFail();
        $settings = $category->settings()->active()->public()->ordered()->get()->skipWhile(fn ($setting): bool => // Skip system settings that are not properly configured for display
            empty($setting->key) || ! $setting->is_active || ! $setting->is_public || empty($setting->group) || empty($setting->name))->paginate(20);
        $relatedCategories = SystemSettingCategory::active()->where('id', '!=', $category->id)->limit(5)->get();

        return view('system-settings.category', ['category' => $category, 'settings' => $settings, 'relatedCategories' => $relatedCategories]);
    }

    /**
     * Handle group functionality with proper error handling.
     */
    public function group(string $group): View
    {
        $settings = SystemSetting::active()->public()->where('group', $group)->ordered()->get()->skipWhile(fn ($setting): bool => // Skip system settings that are not properly configured for display
            empty($setting->key) || ! $setting->is_active || ! $setting->is_public || empty($setting->group) || empty($setting->name))->paginate(20);
        $categories = SystemSettingCategory::active()->withCount(['settings' => function ($query) use ($group): void {
            $query->where('group', $group)->active()->public();
        }])->having('settings_count', '>', 0)->get();

        return view('system-settings.group', ['settings' => $settings, 'categories' => $categories, 'group' => $group]);
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

        return view('system-settings.search', ['settings' => $settings, 'categories' => $categories, 'query' => $query]);
    }

    #[OA\Get(
        path: '/system-settings',
        summary: 'List public system settings',
        description: 'Return key-value pairs for public system settings. Results can be filtered by group, category, or key whitelist.',
        tags: ['System Settings'],
        parameters: [
            new OA\QueryParameter(name: 'group', description: 'Filter settings by group slug.', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'category', description: 'Filter by category slug.', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'keys', description: 'Comma separated list of keys to include.', in: 'query', schema: new OA\Schema(type: 'string')),
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
            new OA\Response(ref: '#/components/responses/SystemSettingsIndex', response: 200),
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
        })->get()->mapWithKeys(fn ($setting): array => [$setting->key => $setting->value]);

        return response()->json($settings);
    }

    #[OA\Get(
        path: '/system-settings/{key}',
        summary: 'Retrieve a single system setting',
        tags: ['System Settings'],
        parameters: [
            new OA\Parameter(name: 'key', description: 'System setting key.', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
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
            new OA\Response(ref: '#/components/responses/SystemSettingItem', response: 200),
            new OA\Response(ref: '#/components/responses/SystemSettingNotFound', response: 404),
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
            new OA\Response(ref: '#/components/responses/SystemSettingCategories', response: 200),
        ]
    )]
    public function categories(): JsonResponse
    {
        $categories = SystemSettingCategory::active()->withCount(['settings' => function ($query): void {
            $query->active()->public();
        }])->having('settings_count', '>', 0)->ordered()->get()->map(fn ($category): array => ['id' => $category->id, 'name' => $category->getTranslatedName(), 'slug' => $category->slug, 'description' => $category->getTranslatedDescription(), 'icon' => $category->getIconClass(), 'color' => $category->color, 'settings_count' => $category->settings_count]);

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
            new OA\Response(ref: '#/components/responses/SystemSettingGroups', response: 200),
        ]
    )]
    public function groups(): JsonResponse
    {
        $groups = SystemSetting::active()->public()->select('group')->selectRaw('count(*) as settings_count')->groupBy('group')->orderBy('settings_count', 'desc')->get()->map(fn ($group): array => ['name' => $group->group, 'label' => ucfirst((string) $group->group), 'settings_count' => $group->settings_count]);

        return response()->json($groups);
    }
}
