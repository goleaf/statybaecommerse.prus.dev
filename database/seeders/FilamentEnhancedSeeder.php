<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Legal;
use App\Models\Product;
use App\Models\Translations\LegalTranslation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class FilamentEnhancedSeeder extends Seeder
{
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
        $permissionNames = [
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

        collect($permissionNames)->each(fn (string $name) => Permission::firstOrCreate(['name' => $name]));
    }

    private function seedRoles(): void
    {
        // Create roles with their permissions using factories
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
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
        ]);

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
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
        ]);

        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->givePermissionTo([
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
        ]);
    }

    private function seedAdminUsers(): void
    {
        // Create admin users using factories with role assignment
        $superAdmin = User::factory()
            ->state([
                'email' => 'admin@example.com',
                'name' => 'Super Admin',
                'is_admin' => true,
                'is_active' => true,
                'timezone' => 'Europe/Vilnius',
                'preferred_locale' => 'lt',
                'email_verified_at' => now(),
            ])
            ->create();
        $superAdmin->assignRole('super_admin');

        $admin = User::factory()
            ->state([
                'email' => 'admin.user@example.com',
                'name' => 'Admin User',
                'is_admin' => true,
                'is_active' => true,
                'timezone' => 'Europe/Vilnius',
                'preferred_locale' => 'lt',
                'email_verified_at' => now(),
            ])
            ->create();
        $admin->assignRole('admin');

        $manager = User::factory()
            ->state([
                'email' => 'manager@example.com',
                'name' => 'Manager User',
                'is_admin' => true,
                'is_active' => true,
                'timezone' => 'Europe/Vilnius',
                'preferred_locale' => 'lt',
                'email_verified_at' => now(),
            ])
            ->create();
        $manager->assignRole('manager');
    }

    private function enhanceExistingData(): void
    {
        // Enhance existing products using factory states
        Product::chunk(50, function ($products): void {
            foreach ($products as $product) {
                $product->update([
                    'meta_title' => $product->name,
                    'meta_description' => $product->description ? substr(strip_tags($product->description), 0, 160) : null,
                    'is_featured' => fake()->boolean(20),
                    'sort_order' => fake()->numberBetween(1, 1000),
                    'track_inventory' => true,
                    'low_stock_threshold' => fake()->numberBetween(3, 10),
                    'available_from' => fake()->optional(0.8)->dateTimeBetween('-1 month', 'now'),
                    'available_until' => fake()->optional(0.2)->dateTimeBetween('now', '+1 year'),
                ]);
            }
        });

        // Enhance existing categories
        Category::chunk(50, function ($categories): void {
            foreach ($categories as $category) {
                $category->update([
                    'meta_title' => $category->name,
                    'meta_description' => $category->description ? substr(strip_tags($category->description), 0, 160) : null,
                    'is_featured' => fake()->boolean(30),
                    'sort_order' => fake()->numberBetween(1, 100),
                    'icon' => fake()->optional(0.7)->randomElement([
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
                    'meta_title' => $brand->name,
                    'meta_description' => $brand->description ? substr(strip_tags($brand->description), 0, 160) : null,
                    'is_featured' => fake()->boolean(25),
                    'sort_order' => fake()->numberBetween(1, 100),
                    'website' => fake()->optional(0.6)->url(),
                    'contact_email' => fake()->optional(0.4)->companyEmail(),
                    'contact_phone' => fake()->optional(0.3)->phoneNumber(),
                ]);
            }
        });
    }

    private function seedLegalPages(): void
    {
        // Create legal pages using factories with translations
        $privacyPolicy = Legal::factory()
            ->state(['key' => 'privacy-policy', 'is_enabled' => true])
            ->has(
                LegalTranslation::factory()->state([
                    'locale' => 'en',
                    'title' => 'Privacy Policy',
                    'slug' => 'privacy-policy',
                    'content' => '<h1>Privacy Policy</h1><p>This is our privacy policy content. We respect your privacy and are committed to protecting your personal data.</p>',
                ]),
                'translations'
            )
            ->has(
                LegalTranslation::factory()->state([
                    'locale' => 'lt',
                    'title' => 'Privatumo politika',
                    'slug' => 'privatumo-politika',
                    'content' => '<h1>Privatumo politika</h1><p>Čia yra mūsų privatumo politikos turinys. Mes gerbiame jūsų privatumą ir įsipareigojame saugoti jūsų asmens duomenis.</p>',
                ]),
                'translations'
            )
            ->has(
                LegalTranslation::factory()->state([
                    'locale' => 'de',
                    'title' => 'Datenschutzrichtlinie',
                    'slug' => 'datenschutzrichtlinie',
                    'content' => '<h1>Datenschutzrichtlinie</h1><p>Dies ist der Inhalt unserer Datenschutzrichtlinie. Wir respektieren Ihre Privatsphäre und verpflichten uns, Ihre persönlichen Daten zu schützen.</p>',
                ]),
                'translations'
            )
            ->create();

        $termsOfService = Legal::factory()
            ->state(['key' => 'terms-of-service', 'is_enabled' => true])
            ->has(
                LegalTranslation::factory()->state([
                    'locale' => 'en',
                    'title' => 'Terms of Service',
                    'slug' => 'terms-of-service',
                    'content' => '<h1>Terms of Service</h1><p>These are our terms of service. By using our website, you agree to these terms.</p>',
                ]),
                'translations'
            )
            ->has(
                LegalTranslation::factory()->state([
                    'locale' => 'lt',
                    'title' => 'Paslaugų teikimo sąlygos',
                    'slug' => 'paslaugu-teikimo-salygos',
                    'content' => '<h1>Paslaugų teikimo sąlygos</h1><p>Tai mūsų paslaugų teikimo sąlygos. Naudodamiesi mūsų svetaine, sutinkate su šiomis sąlygomis.</p>',
                ]),
                'translations'
            )
            ->has(
                LegalTranslation::factory()->state([
                    'locale' => 'de',
                    'title' => 'Nutzungsbedingungen',
                    'slug' => 'nutzungsbedingungen',
                    'content' => '<h1>Nutzungsbedingungen</h1><p>Dies sind unsere Nutzungsbedingungen. Durch die Nutzung unserer Website stimmen Sie diesen Bedingungen zu.</p>',
                ]),
                'translations'
            )
            ->create();

        $cookiePolicy = Legal::factory()
            ->state(['key' => 'cookie-policy', 'is_enabled' => true])
            ->has(
                LegalTranslation::factory()->state([
                    'locale' => 'en',
                    'title' => 'Cookie Policy',
                    'slug' => 'cookie-policy',
                    'content' => '<h1>Cookie Policy</h1><p>This is our cookie policy. We use cookies to improve your experience on our website.</p>',
                ]),
                'translations'
            )
            ->has(
                LegalTranslation::factory()->state([
                    'locale' => 'lt',
                    'title' => 'Slapukų politika',
                    'slug' => 'slapuku-politika',
                    'content' => '<h1>Slapukų politika</h1><p>Tai mūsų slapukų politika. Naudojame slapukus, kad pagerintume jūsų patirtį mūsų svetainėje.</p>',
                ]),
                'translations'
            )
            ->has(
                LegalTranslation::factory()->state([
                    'locale' => 'de',
                    'title' => 'Cookie-Richtlinie',
                    'slug' => 'cookie-richtlinie',
                    'content' => '<h1>Cookie-Richtlinie</h1><p>Dies ist unsere Cookie-Richtlinie. Wir verwenden Cookies, um Ihre Erfahrung auf unserer Website zu verbessern.</p>',
                ]),
                'translations'
            )
            ->create();
    }
}
