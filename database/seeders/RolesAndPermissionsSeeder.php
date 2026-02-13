<?php

declare(strict_types=1);

namespace Database\Seeders;

/**
 * Backward-compatible alias for legacy tests that still reference the old seeder name.
 */
final class RolesAndPermissionsSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->call(AdminAuthorizationSeeder::class);
    }
}
