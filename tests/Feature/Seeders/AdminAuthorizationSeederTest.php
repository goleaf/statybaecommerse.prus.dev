<?php

declare(strict_types=1);

use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('admin authorization seeder skips gracefully when permission tables are missing', function (): void {
    Schema::dropIfExists('role_has_permissions');
    Schema::dropIfExists('permissions');
    Schema::dropIfExists('roles');

    $this->seed(AdminAuthorizationSeeder::class);

    expect(Schema::hasTable('permissions'))->toBeFalse();
    expect(Schema::hasTable('roles'))->toBeFalse();
    expect(Schema::hasTable('role_has_permissions'))->toBeFalse();
});
