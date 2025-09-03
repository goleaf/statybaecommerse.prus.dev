<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Legal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class BasicFilamentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();
        $this->seedRoles();
        $this->seedAdminUsers();
        $this->seedLegalPages();
    }

    private function seedPermissions(): void
    {
        $permissions = [
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
            'view_dashboard_stats',
            'export_data',
            'import_data',
            'manage_users',
            'manage_roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function seedRoles(): void
    {
        // Super Admin role
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin role
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $adminPermissions = [
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
            'view_brands', 'create_brands', 'edit_brands', 'delete_brands',
            'view_orders', 'create_orders', 'edit_orders',
            'view_customers', 'create_customers', 'edit_customers',
            'view_legals', 'create_legals', 'edit_legals', 'delete_legals',
            'view_analytics', 'view_dashboard_stats', 'export_data',
        ];
        $admin->givePermissionTo($adminPermissions);

        // Manager role
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $managerPermissions = [
            'view_products', 'edit_products',
            'view_categories', 'edit_categories',
            'view_brands', 'edit_brands',
            'view_orders', 'edit_orders',
            'view_customers', 'edit_customers',
            'view_analytics', 'view_dashboard_stats',
        ];
        $manager->givePermissionTo($managerPermissions);

        // Editor role
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editorPermissions = [
            'view_products', 'create_products', 'edit_products',
            'view_categories', 'create_categories', 'edit_categories',
            'view_brands', 'create_brands', 'edit_brands',
            'view_legals', 'create_legals', 'edit_legals',
        ];
        $editor->givePermissionTo($editorPermissions);
    }

    private function seedAdminUsers(): void
    {
        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'preferred_locale' => 'lt',
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin.user@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'preferred_locale' => 'lt',
            ]
        );
        $admin->assignRole('admin');

        // Create manager user
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'preferred_locale' => 'lt',
            ]
        );
        $manager->assignRole('manager');
    }

    private function seedLegalPages(): void
    {
        $legalPages = [
            [
                'key' => 'privacy-policy',
                'is_enabled' => true,
                'translations' => [
                    'en' => [
                        'title' => 'Privacy Policy',
                        'slug' => 'privacy-policy',
                        'content' => '<h1>Privacy Policy</h1><p>This is our privacy policy content. We respect your privacy and are committed to protecting your personal data.</p>',
                    ],
                    'lt' => [
                        'title' => 'Privatumo politika',
                        'slug' => 'privatumo-politika',
                        'content' => '<h1>Privatumo politika</h1><p>Čia yra mūsų privatumo politikos turinys. Mes gerbiame jūsų privatumą ir įsipareigojame saugoti jūsų asmens duomenis.</p>',
                    ],
                ],
            ],
            [
                'key' => 'terms-of-service',
                'is_enabled' => true,
                'translations' => [
                    'en' => [
                        'title' => 'Terms of Service',
                        'slug' => 'terms-of-service',
                        'content' => '<h1>Terms of Service</h1><p>These are our terms of service. By using our website, you agree to these terms.</p>',
                    ],
                    'lt' => [
                        'title' => 'Paslaugų teikimo sąlygos',
                        'slug' => 'paslaugu-teikimo-salygos',
                        'content' => '<h1>Paslaugų teikimo sąlygos</h1><p>Tai mūsų paslaugų teikimo sąlygos. Naudodamiesi mūsų svetaine, sutinkate su šiomis sąlygomis.</p>',
                    ],
                ],
            ],
            [
                'key' => 'cookie-policy',
                'is_enabled' => true,
                'translations' => [
                    'en' => [
                        'title' => 'Cookie Policy',
                        'slug' => 'cookie-policy',
                        'content' => '<h1>Cookie Policy</h1><p>This is our cookie policy. We use cookies to improve your experience on our website.</p>',
                    ],
                    'lt' => [
                        'title' => 'Slapukų politika',
                        'slug' => 'slapuku-politika',
                        'content' => '<h1>Slapukų politika</h1><p>Tai mūsų slapukų politika. Naudojame slapukus, kad pagerintume jūsų patirtį mūsų svetainėje.</p>',
                    ],
                ],
            ],
        ];

        foreach ($legalPages as $pageData) {
            $legal = Legal::firstOrCreate(
                ['key' => $pageData['key']],
                ['is_enabled' => $pageData['is_enabled']]
            );

            foreach ($pageData['translations'] as $locale => $translation) {
                $legal->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $translation
                );
            }
        }
    }
}
