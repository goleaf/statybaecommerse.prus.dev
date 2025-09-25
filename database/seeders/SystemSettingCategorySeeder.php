<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use Illuminate\Database\Seeder;

use function collect;

final class SystemSettingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect($this->categoryDefinitions())->each(function (array $definition): void {
            SystemSettingCategory::withTrashed()
                ->where('slug', $definition['attributes']['slug'])
                ->forceDelete();

            $category = SystemSettingCategory::factory()
                ->state($definition['attributes'])
                ->create();

            SystemSettingCategoryTranslation::factory()
                ->count(count($definition['translations']))
                ->sequence(...$definition['translations'])
                ->for($category)
                ->create();
        });
    }

    private function categoryDefinitions(): array
    {
        return [
            [
                'attributes' => [
                    'name' => 'General',
                    'slug' => 'general',
                    'description' => 'General system settings and configurations',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'color' => 'primary',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                'translations' => $this->translationsFor('general'),
            ],
            [
                'attributes' => [
                    'name' => 'Security',
                    'slug' => 'security',
                    'description' => 'Security-related settings and configurations',
                    'icon' => 'heroicon-o-shield-check',
                    'color' => 'danger',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                'translations' => $this->translationsFor('security'),
            ],
            [
                'attributes' => [
                    'name' => 'Performance',
                    'slug' => 'performance',
                    'description' => 'Performance optimization settings',
                    'icon' => 'heroicon-o-bolt',
                    'color' => 'warning',
                    'sort_order' => 3,
                    'is_active' => true,
                ],
                'translations' => $this->translationsFor('performance'),
            ],
            [
                'attributes' => [
                    'name' => 'UI/UX',
                    'slug' => 'ui-ux',
                    'description' => 'User interface and user experience settings',
                    'icon' => 'heroicon-o-paint-brush',
                    'color' => 'info',
                    'sort_order' => 4,
                    'is_active' => true,
                ],
                'translations' => $this->translationsFor('ui-ux'),
            ],
            [
                'attributes' => [
                    'name' => 'API',
                    'slug' => 'api',
                    'description' => 'API configuration and settings',
                    'icon' => 'heroicon-o-globe-alt',
                    'color' => 'success',
                    'sort_order' => 5,
                    'is_active' => true,
                ],
                'translations' => $this->translationsFor('api'),
            ],
            [
                'attributes' => [
                    'name' => 'Database',
                    'slug' => 'database',
                    'description' => 'Database configuration and settings',
                    'icon' => 'heroicon-o-database',
                    'color' => 'secondary',
                    'sort_order' => 6,
                    'is_active' => true,
                ],
                'translations' => $this->translationsFor('database'),
            ],
            [
                'attributes' => [
                    'name' => 'Authentication',
                    'slug' => 'authentication',
                    'description' => 'Authentication and authorization settings',
                    'icon' => 'heroicon-o-key',
                    'color' => 'danger',
                    'sort_order' => 7,
                    'is_active' => true,
                ],
                'translations' => $this->translationsFor('authentication'),
            ],
            [
                'attributes' => [
                    'name' => 'Analytics',
                    'slug' => 'analytics',
                    'description' => 'Analytics and reporting settings',
                    'icon' => 'heroicon-o-chart-bar',
                    'color' => 'info',
                    'sort_order' => 8,
                    'is_active' => true,
                ],
                'translations' => $this->translationsFor('analytics'),
            ],
        ];
    }

    private function translationsFor(string $slug): array
    {
        return [
            ['locale' => 'lt', 'name' => $this->lithuanianName($slug), 'description' => $this->lithuanianDescription($slug)],
            ['locale' => 'en', 'name' => $this->englishName($slug), 'description' => $this->englishDescription($slug)],
        ];
    }

    private function lithuanianName(string $slug): string
    {
        return match ($slug) {
            'general' => 'Bendri nustatymai',
            'security' => 'Saugumo nustatymai',
            'performance' => 'Našumo nustatymai',
            'ui-ux' => 'Vartotojo sąsajos nustatymai',
            'api' => 'API nustatymai',
            'database' => 'Duomenų bazės nustatymai',
            'authentication' => 'Autentifikavimo nustatymai',
            'analytics' => 'Analitikos nustatymai',
            default => 'Nustatymai',
        };
    }

    private function lithuanianDescription(string $slug): string
    {
        return match ($slug) {
            'general' => 'Bendri sistemos nustatymai ir konfigūracija.',
            'security' => 'Saugumo ir prieigos kontrolės nustatymai.',
            'performance' => 'Našumo optimizavimo nustatymai.',
            'ui-ux' => 'Vartotojo sąsajos ir dizaino nustatymai.',
            'api' => 'API integracijų ir ribojimų nustatymai.',
            'database' => 'Duomenų bazės konfigūracijos nustatymai.',
            'authentication' => 'Autentifikavimo ir autorizacijos nustatymai.',
            'analytics' => 'Analitikos ir ataskaitų nustatymai.',
            default => 'Sistema nustatymai.',
        };
    }

    private function englishName(string $slug): string
    {
        return match ($slug) {
            'general' => 'General Settings',
            'security' => 'Security Settings',
            'performance' => 'Performance Settings',
            'ui-ux' => 'UI/UX Settings',
            'api' => 'API Settings',
            'database' => 'Database Settings',
            'authentication' => 'Authentication Settings',
            'analytics' => 'Analytics Settings',
            default => 'Settings',
        };
    }

    private function englishDescription(string $slug): string
    {
        return match ($slug) {
            'general' => 'General system settings and configuration.',
            'security' => 'Security and access control settings.',
            'performance' => 'Performance optimization settings.',
            'ui-ux' => 'User interface and design settings.',
            'api' => 'API integration and rate limit settings.',
            'database' => 'Database configuration settings.',
            'authentication' => 'Authentication and authorization settings.',
            'analytics' => 'Analytics and reporting settings.',
            default => 'System settings.',
        };
    }
}
