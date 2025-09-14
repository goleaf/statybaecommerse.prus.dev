<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final /**
 * SystemSettingsService
 * 
 * Service class containing business logic and external integrations.
 */
class SystemSettingsService
{
    private const CACHE_KEY = 'system_settings';

    private const CACHE_TTL = 3600; // 1 hour

    public function get(string $key, $default = null)
    {
        $settings = $this->getAllSettings();

        return $settings[$key] ?? $default;
    }

    public function set(string $key, $value, array $options = []): void
    {
        try {
            DB::beginTransaction();

            $defaults = [
                'type' => 'string',
                'group' => 'general',
                'is_public' => false,
                'is_required' => false,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
            ];

            $data = array_merge($defaults, $options, [
                'key' => $key,
                'value' => $value,
                'updated_by' => auth()->id(),
            ]);

            SystemSetting::updateOrCreate(
                ['key' => $key],
                $data
            );

            $this->clearCache();

            DB::commit();

            Log::info("System setting updated: {$key}", [
                'key' => $key,
                'updated_by' => auth()->id(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update system setting: {$key}", [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getPublic(string $key, $default = null)
    {
        $publicSettings = $this->getPublicSettings();

        return $publicSettings[$key] ?? $default;
    }

    public function getAllSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return SystemSetting::active()
                ->get()
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public function getPublicSettings(): array
    {
        return Cache::remember(self::CACHE_KEY.'_public', self::CACHE_TTL, function () {
            return SystemSetting::active()
                ->public()
                ->get()
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public function getSettingsByGroup(string $group): array
    {
        return Cache::remember(self::CACHE_KEY."_group_{$group}", self::CACHE_TTL, function () use ($group) {
            return SystemSetting::active()
                ->byGroup($group)
                ->get()
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public function getSettingsByCategory(string $category): array
    {
        return Cache::remember(self::CACHE_KEY."_category_{$category}", self::CACHE_TTL, function () use ($category) {
            return SystemSetting::active()
                ->byCategory($category)
                ->get()
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public function bulkUpdate(array $settings): void
    {
        try {
            DB::beginTransaction();

            foreach ($settings as $key => $value) {
                SystemSetting::where('key', $key)
                    ->update([
                        'value' => $value,
                        'updated_by' => auth()->id(),
                    ]);
            }

            $this->clearCache();

            DB::commit();

            Log::info('Bulk system settings update completed', [
                'settings_count' => count($settings),
                'updated_by' => auth()->id(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk update system settings', [
                'error' => $e->getMessage(),
                'updated_by' => auth()->id(),
            ]);
            throw $e;
        }
    }

    public function resetToDefaults(): void
    {
        try {
            DB::beginTransaction();

            $settings = SystemSetting::whereNotNull('default_value')->get();

            foreach ($settings as $setting) {
                $setting->update([
                    'value' => $setting->default_value,
                    'updated_by' => auth()->id(),
                ]);
            }

            $this->clearCache();

            DB::commit();

            Log::info('System settings reset to defaults', [
                'settings_count' => $settings->count(),
                'updated_by' => auth()->id(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reset system settings to defaults', [
                'error' => $e->getMessage(),
                'updated_by' => auth()->id(),
            ]);
            throw $e;
        }
    }

    public function exportSettings(): array
    {
        return SystemSetting::active()
            ->get()
            ->map(function ($setting) {
                return [
                    'key' => $setting->key,
                    'name' => $setting->name,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'group' => $setting->group,
                    'description' => $setting->description,
                    'help_text' => $setting->help_text,
                    'is_public' => $setting->is_public,
                    'is_required' => $setting->is_required,
                    'is_encrypted' => $setting->is_encrypted,
                    'is_readonly' => $setting->is_readonly,
                    'options' => $setting->options,
                    'default_value' => $setting->default_value,
                ];
            })
            ->toArray();
    }

    public function importSettings(array $settings): void
    {
        try {
            DB::beginTransaction();

            foreach ($settings as $settingData) {
                if (isset($settingData['key'])) {
                    SystemSetting::updateOrCreate(
                        ['key' => $settingData['key']],
                        array_merge($settingData, [
                            'updated_by' => auth()->id(),
                        ])
                    );
                }
            }

            $this->clearCache();

            DB::commit();

            Log::info('System settings imported', [
                'settings_count' => count($settings),
                'updated_by' => auth()->id(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to import system settings', [
                'error' => $e->getMessage(),
                'updated_by' => auth()->id(),
            ]);
            throw $e;
        }
    }

    public function validateSetting(string $key, $value): bool
    {
        $setting = SystemSetting::where('key', $key)->first();

        if (! $setting) {
            return false;
        }

        $rules = $setting->getValidationRulesArray();

        if (empty($rules)) {
            return true;
        }

        $validator = validator([$key => $value], [$key => $rules]);

        return ! $validator->fails();
    }

    public function getSettingMetadata(string $key): ?array
    {
        $setting = SystemSetting::where('key', $key)->first();

        if (! $setting) {
            return null;
        }

        return [
            'key' => $setting->key,
            'name' => $setting->name,
            'type' => $setting->type,
            'group' => $setting->group,
            'description' => $setting->description,
            'help_text' => $setting->help_text,
            'is_public' => $setting->is_public,
            'is_required' => $setting->is_required,
            'is_encrypted' => $setting->is_encrypted,
            'is_readonly' => $setting->is_readonly,
            'options' => $setting->getOptionsArray(),
            'default_value' => $setting->default_value,
            'validation_rules' => $setting->getValidationRulesArray(),
        ];
    }

    public function getCategoriesWithSettings(): array
    {
        return Cache::remember(self::CACHE_KEY.'_categories', self::CACHE_TTL, function () {
            return SystemSettingCategory::with(['settings' => function ($query) {
                $query->active()->ordered();
            }])->active()->ordered()->get()->toArray();
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY.'_public');

        // Clear group-specific caches
        $groups = SystemSetting::distinct()->pluck('group');
        foreach ($groups as $group) {
            Cache::forget(self::CACHE_KEY."_group_{$group}");
        }

        // Clear category-specific caches
        $categories = SystemSettingCategory::pluck('slug');
        foreach ($categories as $category) {
            Cache::forget(self::CACHE_KEY."_category_{$category}");
        }

        Cache::forget(self::CACHE_KEY.'_categories');
    }

    public function getSettingsStats(): array
    {
        return [
            'total' => SystemSetting::count(),
            'active' => SystemSetting::active()->count(),
            'public' => SystemSetting::public()->count(),
            'encrypted' => SystemSetting::where('is_encrypted', true)->count(),
            'categories' => SystemSettingCategory::active()->count(),
            'by_type' => SystemSetting::selectRaw('type, count(*) as count')
                ->active()
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_group' => SystemSetting::selectRaw('group, count(*) as count')
                ->active()
                ->groupBy('group')
                ->pluck('count', 'group')
                ->toArray(),
        ];
    }
}
