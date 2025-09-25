<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Legal;
use App\Models\Translations\LegalTranslation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class BasicFilamentSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
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
            'delete_orders',
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
            'view_legals',
            'create_legals',
            'edit_legals',
            'delete_legals',
            'view_settings',
            'edit_settings',
            'view_analytics',
            'view_dashboard_stats',
            'export_data',
            'import_data',
            'manage_users',
            'manage_roles',
        ]);

        $permissions->each(fn (string $name) => Permission::query()->firstOrCreate(['name' => $name]));

        $superAdmin = Role::query()->firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions($permissions);

        $admin = Role::query()->firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions->except(['delete_orders', 'delete_customers', 'manage_roles']));

        $manager = Role::query()->firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
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
            'view_dashboard_stats',
        ]);

        $editor = Role::query()->firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions([
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

        $this->seedAdminUser('admin@example.com', 'Super Admin', 'super_admin');
        $this->seedAdminUser('admin.user@example.com', 'Admin User', 'admin');
        $this->seedAdminUser('manager@example.com', 'Manager User', 'manager');

        collect([
            'privacy-policy' => [
                'lt' => [
                    'title' => 'Privatumo politika',
                    'slug' => 'privatumo-politika',
                    'content' => '<h1>Privatumo politika</h1><p>Mes saugome jūsų asmens duomenis.</p>',
                ],
                'en' => [
                    'title' => 'Privacy Policy',
                    'slug' => 'privacy-policy',
                    'content' => '<h1>Privacy Policy</h1><p>We respect your privacy and protect your data.</p>',
                ],
            ],
            'terms-of-service' => [
                'lt' => [
                    'title' => 'Paslaugų teikimo sąlygos',
                    'slug' => 'paslaugu-teikimo-salygos',
                    'content' => '<h1>Paslaugų teikimo sąlygos</h1><p>Naudodamiesi svetaine sutinkate su šiomis sąlygomis.</p>',
                ],
                'en' => [
                    'title' => 'Terms of Service',
                    'slug' => 'terms-of-service',
                    'content' => '<h1>Terms of Service</h1><p>By using the site you agree to these terms.</p>',
                ],
            ],
            'cookie-policy' => [
                'lt' => [
                    'title' => 'Slapukų politika',
                    'slug' => 'slapuku-politika',
                    'content' => '<h1>Slapukų politika</h1><p>Naudojame slapukus geresnei patirčiai.</p>',
                ],
                'en' => [
                    'title' => 'Cookie Policy',
                    'slug' => 'cookie-policy',
                    'content' => '<h1>Cookie Policy</h1><p>We use cookies to improve your experience.</p>',
                ],
            ],
        ])->each(function (array $translations, string $key): void {
            $legal = Legal::query()->updateOrCreate(
                ['key' => $key],
                ['is_enabled' => true, 'is_required' => $key !== 'cookie-policy']
            );

            collect($translations)->each(function (array $translation, string $locale) use ($legal): void {
                LegalTranslation::query()->updateOrCreate(
                    [
                        'legal_id' => $legal->getKey(),
                        'locale' => $locale,
                    ],
                    $translation + ['seo_title' => $translation['title'], 'seo_description' => $translation['title'].' – '.$translation['slug']]
                );
            });
        });
    }

    private function seedAdminUser(string $email, string $name, string $role): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'preferred_locale' => 'lt',
            ]
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }
    }
}
