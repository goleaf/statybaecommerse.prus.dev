<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('discount_codes')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $isSqlite = $driver === 'sqlite';

        $existingIndexes = [];
        if ($isSqlite) {
            $existingIndexes = collect(DB::select("PRAGMA index_list('discount_codes')"))
                ->map(fn ($row) => $row->name)
                ->all();
        }

        $hasCreatedByIndex = $isSqlite ? in_array('discount_codes_created_by_index', $existingIndexes, true) : false;
        $hasUpdatedByIndex = $isSqlite ? in_array('discount_codes_updated_by_index', $existingIndexes, true) : false;

        Schema::table('discount_codes', function (Blueprint $table) use (
            $isSqlite,
            $hasCreatedByIndex,
            $hasUpdatedByIndex
        ) {
            // Add multi-language description fields
            if (! Schema::hasColumn('discount_codes', 'description_lt')) {
                $table->text('description_lt')->nullable()->after('code');
            }
            if (! Schema::hasColumn('discount_codes', 'description_en')) {
                $table->text('description_en')->nullable()->after('description_lt');
            }

            // Add new fields for enhanced functionality
            if (! Schema::hasColumn('discount_codes', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('description_en');
            }
            $table->integer('usage_limit')->nullable()->change();
            if (! Schema::hasColumn('discount_codes', 'usage_limit_per_user')) {
                $table->integer('usage_limit_per_user')->nullable()->after('usage_limit');
            }
            if (! Schema::hasColumn('discount_codes', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('usage_limit_per_user');
            }
            if (! Schema::hasColumn('discount_codes', 'status')) {
                $table->string('status')->default('active')->after('is_active');
            }
            if (! Schema::hasColumn('discount_codes', 'metadata')) {
                $table->json('metadata')->nullable()->after('status');
            }

            // Add tracking fields
            if (! Schema::hasColumn('discount_codes', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('metadata');
            }
            if (! Schema::hasColumn('discount_codes', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }

            // Add soft deletes
            if (! Schema::hasColumn('discount_codes', 'deleted_at')) {
                $table->softDeletes();
            }

            // Only add indexes for non-SQLite to avoid duplicate index creation in tests
            if (! $isSqlite) {
                if (Schema::hasColumn('discount_codes', 'created_by') && ! $hasCreatedByIndex) {
                    $table->index('created_by');
                }
                if (Schema::hasColumn('discount_codes', 'updated_by') && ! $hasUpdatedByIndex) {
                    $table->index('updated_by');
                }
            }

            // Add foreign key constraints
            if (! $isSqlite) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('discount_codes')) {
            return;
        }

        Schema::table('discount_codes', function (Blueprint $table) {
            // Drop FKs if exist
            try {
                $table->dropForeign(['created_by']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['updated_by']);
            } catch (\Throwable $e) {
            }

            // Drop named indexes if present
            try {
                $table->dropIndex('discount_codes_created_by_index');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('discount_codes_updated_by_index');
            } catch (\Throwable $e) {
            }

            // Soft deletes column
            try {
                $table->dropSoftDeletes();
            } catch (\Throwable $e) {
            }

            $columns = [
                'description_lt',
                'description_en',
                'starts_at',
                'usage_limit_per_user',
                'is_active',
                'status',
                'metadata',
                'created_by',
                'updated_by',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('discount_codes', $col)) {
                    try {
                        $table->dropColumn($col);
                    } catch (\Throwable $e) {
                    }
                }
            }
        });
    }
};
