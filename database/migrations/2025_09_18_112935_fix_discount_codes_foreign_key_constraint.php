<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if we're using SQLite or MySQL
        $isSQLite = DB::getDriverName() === 'sqlite';

        if ($isSQLite) {
            // Disable foreign key checks for SQLite
            DB::statement('PRAGMA foreign_keys=OFF');
        } else {
            // Disable foreign key checks for MySQL
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            // Drop the old foreign key constraint
            if (Schema::hasTable('discount_codes')) {
                // Recreate the table with correct foreign key constraint
                Schema::dropIfExists('discount_codes');

                Schema::create('discount_codes', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
                    $table->string('code')->unique();
                    $table->timestamp('expires_at')->nullable();
                    $table->unsignedInteger('max_uses')->nullable();
                    $table->unsignedInteger('usage_count')->default(0);
                    $table->json('metadata')->nullable();
                    $table->timestamps();
                    $table->string('description_lt')->nullable();
                    $table->string('description_en')->nullable();
                    $table->timestamp('starts_at')->nullable();
                    $table->unsignedInteger('usage_limit_per_user')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->string('status')->default('active');
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->softDeletes();

                    $table->index(['is_active', 'status']);
                    $table->index(['starts_at', 'expires_at']);
                    $table->index(['created_by']);
                    $table->index(['updated_by']);
                });
            }
        } finally {
            // Re-enable foreign key checks
            if ($isSQLite) {
                DB::statement('PRAGMA foreign_keys=ON');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }

    public function down(): void
    {
        // This migration is not reversible as it fixes a broken constraint
        // The original constraint was broken and needs to be fixed
    }
};
