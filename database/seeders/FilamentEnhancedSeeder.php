<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Legal;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Product;
use App\Models\Translations\LegalTranslation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class FilamentEnhancedSeeder extends Seeder
{
    /**
     * The Filament admin panel relies on the dedicated `admin` guard, so the
     * seeder keeps the value centralised to avoid drifting back to the
     * framework's default `web` guard when new permissions are added.
     */
    private const ADMIN_GUARD = 'admin';

    /**
     * Canonical permission names shared across Filament roles. Keeping them in
     * a class constant allows role-specific subsets to reuse the same source
     * list while ensuring new permissions are seeded against the correct
     * guard.
     *
     * @var list<string>
     */
    private const PERMISSION_NAMES = [
        // Product permissions
        'view_products',
        'create_products',
        'edit_products',
        'delete_products',
        'bulk_delete_products',
        // Category permissions
        'view_categories',
        'create_categories',
        'edit_categories',
        'delete_categories',
        'bulk_delete_categories',
        // Brand permissions
        'view_brands',
        'create_brands',
        'edit_brands',
        'delete_brands',
        'bulk_delete_brands',
        // Order permissions
        'view_orders',
        'create_orders',
        'edit_orders',
        'delete_orders',
        'bulk_delete_orders',
        // Customer permissions
        'view_customers',
        'create_customers',
        'edit_customers',
        'delete_customers',
        'bulk_delete_customers',
        // Legal pages permissions
        'view_legals',
        'create_legals',
        'edit_legals',
        'delete_legals',
        'bulk_delete_legals',
        // System permissions
        'view_settings',
        'edit_settings',
        'view_analytics',
        'export_data',
        'import_data',
        'manage_users',
        'manage_roles',
    ];

    public function run(): void
    {
        $this->seedPermissions();
        $this->seedRoles();
        $this->seedAdminUsers();
        $this->enhanceExistingData();
        $this->seedLegalPages();
    }

    private function seedPermissions(): void
    {
        collect(self::PERMISSION_NAMES)->each(
            function (string $name): void {
                // Explicitly persist permissions against the admin guard to
                // avoid `GuardDoesNotMatch` exceptions when roles are synced.
                Permission::query()->firstOrCreate([
                    'name'       => $name,
                    'guard_name' => self::ADMIN_GUARD,
                ]);
            }
        );
    }

    private function seedRoles(): void
    {
        $roleDefinitions = [
            // Super administrators receive the complete permission matrix.
            'super_admin' => self::PERMISSION_NAMES,
            // Admins retain management access but avoid destructive role edits.
            'admin'       => [
                'view_products',
                'create_products',
                'edit_products',
                'delete_products',
                'view_categories',
                'create_categories',
                'edit_categories',
                'delete_categories',
                'view_brands',
                'create_brands',
                'edit_brands',
                'delete_brands',
                'view_orders',
                'create_orders',
                'edit_orders',
                'view_customers',
                'create_customers',
                'edit_customers',
                'view_legals',
                'create_legals',
                'edit_legals',
                'delete_legals',
                'view_analytics',
                'export_data',
            ],
            // Managers cover day-to-day catalogue operations without deletes.
            'manager'     => [
                'view_products',
                'edit_products',
                'view_categories',
                'edit_categories',
                'view_brands',
                'edit_brands',
                'view_orders',
                'edit_orders',
                'view_customers',
                'edit_customers',
                'view_analytics',
            ],
            // Editors focus on creating catalogue data without destructive powers.
            'editor'      => [
                'view_products',
                'create_products',
                'edit_products',
                'view_categories',
                'create_categories',
                'edit_categories',
                'view_brands',
                'create_brands',
                'edit_brands',
                'view_legals',
                'create_legals',
                'edit_legals',
            ],
        ];

        foreach ($roleDefinitions as $roleName => $permissions) {
            $role = Role::query()->firstOrCreate([
                'name'       => $roleName,
                'guard_name' => self::ADMIN_GUARD,
            ]);

            // Sync by permission names so guard-aware lookups remain intact.
            $role->syncPermissions($permissions);
        }
    }

    private function seedAdminUsers(): void
    {
        // Maintain a single deterministic admin account for browser-driven QA.
        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('admin123'),
                'is_admin'          => true,
                'is_active'         => true,
                'timezone'          => 'Europe/Vilnius',
                'preferred_locale'  => 'lt',
                'email_verified_at' => now(),
            ]
        );

        if (! $superAdmin->hasRole('super_admin')) {
            // Assign the Filament role after ensuring the guard-aware role exists.
            $superAdmin->assignRole('super_admin');
        }
    }

    private function enhanceExistingData(): void
    {
        // Enhance existing products using factory states
        Product::chunk(50, function ($products): void {
            foreach ($products as $product) {
                $product->update([
                    'meta_title'          => $product->name,
                    'meta_description'    => $product->description ? substr(strip_tags($product->description), 0, 160) : null,
                    'is_featured'         => fake()->boolean(20),
                    'sort_order'          => fake()->numberBetween(1, 1000),
                    'track_inventory'     => true,
                    'low_stock_threshold' => fake()->numberBetween(3, 10),
                    'available_from'      => fake()->optional(0.8)->dateTimeBetween('-1 month', 'now'),
                    'available_until'     => fake()->optional(0.2)->dateTimeBetween('now', '+1 year'),
                ]);
            }
        });

        // Enhance existing categories
        Category::chunk(50, function ($categories): void {
            foreach ($categories as $category) {
                $category->update([
                    'meta_title'       => $category->name,
                    'meta_description' => $category->description ? substr(strip_tags($category->description), 0, 160) : null,
                    'is_featured'      => fake()->boolean(30),
                    'sort_order'       => fake()->numberBetween(1, 100),
                    'icon'             => fake()->optional(0.7)->randomElement([
                        'heroicon-o-device-phone-mobile',
                        'heroicon-o-computer-desktop',
                        'heroicon-o-tv',
                        'heroicon-o-camera',
                        'heroicon-o-musical-note',
                        'heroicon-o-home',
                        'heroicon-o-sparkles',
                        'heroicon-o-heart',
                    ]),
                    'color' => fake()->optional(0.5)->hexColor(),
                ]);
            }
        });

        // Enhance existing brands
        Brand::chunk(50, function ($brands): void {
            foreach ($brands as $brand) {
                $brand->update([
                    'meta_title'       => $brand->name,
                    'meta_description' => $brand->description ? substr(strip_tags($brand->description), 0, 160) : null,
                    'is_featured'      => fake()->boolean(25),
                    'sort_order'       => fake()->numberBetween(1, 100),
                    'website'          => fake()->optional(0.6)->url(),
                    'contact_email'    => fake()->optional(0.4)->companyEmail(),
                    'contact_phone'    => fake()->optional(0.3)->phoneNumber(),
                ]);
            }
        });
    }

    private function seedLegalPages(): void
    {
        // Define deterministic legal documents so repeated seed runs stay idempotent.
        $legalPageDefinitions = [
            'privacy-policy'     => [
                'type'          => 'privacy_policy',
                'is_enabled'    => true,
                'is_required'   => true,
                'sort_order'    => 10,
                'meta_data'     => [
                    'version'          => '1.1',
                    'last_reviewed'    => '2025-02-17',
                    'review_frequency' => 'monthly',
                ],
                'published_at'  => now()->subMonths(1),
                'translations'  => [
                    'en' => [
                        'title'   => 'Privacy Policy',
                        'slug'    => 'privacy-policy',
                        'content' => '<h1>Privacy Policy</h1><p>This is our privacy policy content. We respect your privacy and are committed to protecting your personal data.</p>',
                    ],
                    'lt' => [
                        'title'   => 'Privatumo politika',
                        'slug'    => 'privatumo-politika',
                        'content' => '<h1>Privatumo politika</h1><p>Čia yra mūsų privatumo politikos turinys. Mes gerbiame jūsų privatumą ir įsipareigojame saugoti jūsų asmens duomenis.</p>',
                    ],
                    'de' => [
                        'title'   => 'Datenschutzrichtlinie',
                        'slug'    => 'datenschutzrichtlinie',
                        'content' => '<h1>Datenschutzrichtlinie</h1><p>Dies ist der Inhalt unserer Datenschutzrichtlinie. Wir respektieren Ihre Privatsphäre und verpflichten uns, Ihre persönlichen Daten zu schützen.</p>',
                    ],
                ],
            ],
            'terms-of-service'   => [
                'type'          => 'terms_of_use',
                'is_enabled'    => true,
                'is_required'   => true,
                'sort_order'    => 20,
                'meta_data'     => [
                    'version'          => '2.0',
                    'last_reviewed'    => '2025-01-15',
                    'review_frequency' => 'quarterly',
                ],
                'published_at'  => now()->subWeeks(6),
                'translations'  => [
                    'en' => [
                        'title'   => 'Terms of Service',
                        'slug'    => 'terms-of-service',
                        'content' => '<h1>Terms of Service</h1><p>These are our terms of service. By using our website, you agree to these terms.</p>',
                    ],
                    'lt' => [
                        'title'   => 'Paslaugų teikimo sąlygos',
                        'slug'    => 'paslaugu-teikimo-salygos',
                        'content' => '<h1>Paslaugų teikimo sąlygos</h1><p>Tai mūsų paslaugų teikimo sąlygos. Naudodamiesi mūsų svetaine, sutinkate su šiomis sąlygomis.</p>',
                    ],
                    'de' => [
                        'title'   => 'Nutzungsbedingungen',
                        'slug'    => 'nutzungsbedingungen',
                        'content' => '<h1>Nutzungsbedingungen</h1><p>Dies sind unsere Nutzungsbedingungen. Durch die Nutzung unserer Website stimmen Sie diesen Bedingungen zu.</p>',
                    ],
                ],
            ],
            'cookie-policy'      => [
                'type'          => 'cookie_policy',
                'is_enabled'    => true,
                'is_required'   => false,
                'sort_order'    => 30,
                'meta_data'     => [
                    'version'          => '1.0',
                    'last_reviewed'    => '2024-12-01',
                    'review_frequency' => 'annually',
                ],
                'published_at'  => now()->subMonths(2),
                'translations'  => [
                    'en' => [
                        'title'   => 'Cookie Policy',
                        'slug'    => 'cookie-policy',
                        'content' => '<h1>Cookie Policy</h1><p>This is our cookie policy. We use cookies to improve your experience on our website.</p>',
                    ],
                    'lt' => [
                        'title'   => 'Slapukų politika',
                        'slug'    => 'slapuku-politika',
                        'content' => '<h1>Slapukų politika</h1><p>Tai mūsų slapukų politika. Naudojame slapukus, kad pagerintume jūsų patirtį mūsų svetainėje.</p>',
                    ],
                    'de' => [
                        'title'   => 'Cookie-Richtlinie',
                        'slug'    => 'cookie-richtlinie',
                        'content' => '<h1>Cookie-Richtlinie</h1><p>Dies ist unsere Cookie-Richtlinie. Wir verwenden Cookies, um Ihre Erfahrung auf unserer Website zu verbessern.</p>',
                    ],
                ],
            ],
        ];

        foreach ($legalPageDefinitions as $key => $definition) {
            // Prevent duplicate keys by updating existing records instead of blindly creating new ones.
            // Removing the enabled/published global scopes keeps reruns idempotent even if an admin
            // temporarily disables or future-dates a legal entry between seed executions.
            $legal = Legal::query()
                ->withoutGlobalScopes([EnabledScope::class, PublishedScope::class])
                ->updateOrCreate(
                    ['key' => $key],
                    [
                        'type'         => $definition['type'],
                        'is_enabled'   => $definition['is_enabled'],
                        'is_required'  => $definition['is_required'],
                        'sort_order'   => $definition['sort_order'],
                        'meta_data'    => $definition['meta_data'],
                        'published_at' => $definition['published_at'],
                    ]
                );

            // Sync translations locale-by-locale so reruns keep content up to date without creating duplicates.
            foreach ($definition['translations'] as $locale => $translationData) {
                $legal->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $translationData
                );
            }
        }
    }
}
