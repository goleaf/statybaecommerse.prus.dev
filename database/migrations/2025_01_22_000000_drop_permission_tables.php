<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop permission-related tables in correct order (foreign keys first)
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');

        // Remove permissions_matrix column from users table if it exists
        if (Schema::hasColumn('users', 'permissions_matrix')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('permissions_matrix');
            });
        }
    }

    public function down(): void
    {
        // This migration is irreversible as we're completely removing filament-shield
        throw new Exception('Cannot rollback filament-shield removal migration');
    }
};
