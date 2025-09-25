<?php

declare(strict_types=1);

use App\Models\AdminUser;
use Spatie\Permission\Models\Role;

it('assigns and checks roles on admin guard', function (): void {
    $role = Role::query()->create(['name' => 'administrator', 'guard_name' => 'admin']);

    $admin = AdminUser::factory()->create();
    $admin->assignRole($role);

    expect($admin->hasRole('administrator'))->toBeTrue();
});
