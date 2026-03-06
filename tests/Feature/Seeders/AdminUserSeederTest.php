<?php

declare(strict_types=1);

use App\Models\AdminUser;
use Database\Seeders\AdminAuthorizationSeeder;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('seeds curated admin accounts with super admin role and brochure permissions', function (): void {
    $this->seed(AdminAuthorizationSeeder::class);
    $this->seed(AdminUserSeeder::class);

    $admins = AdminUser::query()
        ->orderBy('email')
        ->get();

    expect($admins)->toHaveCount(2);
    expect($admins->pluck('email')->all())->toBe([
        'info@egisstatyba.lt',
        'info@egisstatyba.lt',
    ]);

    foreach ($admins as $admin) {
        expect($admin->hasRole('super_admin'))->toBeTrue();
        expect($admin->can('view_brochures'))->toBeTrue();
    }
});

it('warns and skips role sync when super admin role is missing', function (): void {
    $this->artisan('db:seed', ['--class' => AdminUserSeeder::class])
        ->expectsOutputToContain('AdminUserSeeder: role "super_admin" for guard "admin" is missing.')
        ->assertExitCode(0);

    expect(AdminUser::query()->count())->toBe(2);
    expect(
        DB::table('model_has_roles')
            ->where('model_type', AdminUser::class)
            ->count()
    )->toBe(0);
});

it('is idempotent when run multiple times', function (): void {
    $this->seed(AdminAuthorizationSeeder::class);
    $this->seed(AdminUserSeeder::class);
    $this->seed(AdminUserSeeder::class);

    expect(AdminUser::query()->count())->toBe(2);

    $role = Role::findByName('super_admin', 'admin');

    expect(
        DB::table('model_has_roles')
            ->where('model_type', AdminUser::class)
            ->where('role_id', $role->id)
            ->count()
    )->toBe(2);
});
