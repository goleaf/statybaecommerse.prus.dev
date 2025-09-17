<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('discount_codes')) {
            return;
        }

        Schema::table('discount_codes', function (Blueprint $table): void {
            // Multilanguage fields (LT default + EN)
            if (!Schema::hasColumn('discount_codes', 'description_lt')) {
                $table->text('description_lt')->nullable()->after('code');
            }
            if (!Schema::hasColumn('discount_codes', 'description_en')) {
                $table->text('description_en')->nullable()->after('description_lt');
            }

            // Enhanced fields
            if (!Schema::hasColumn('discount_codes', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('description_en');
            }
            try {
                if (Schema::hasColumn('discount_codes', 'usage_limit')) {
                    $table->integer('usage_limit')->nullable()->change();
                }
            } catch (\Throwable $e) {
            }
            if (!Schema::hasColumn('discount_codes', 'usage_limit_per_user')) {
                $table->integer('usage_limit_per_user')->nullable()->after('usage_limit');
            }
            if (!Schema::hasColumn('discount_codes', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('usage_limit_per_user');
            }
            if (!Schema::hasColumn('discount_codes', 'status')) {
                $table->string('status')->default('active')->after('is_active');
            }
            if (!Schema::hasColumn('discount_codes', 'metadata')) {
                $table->json('metadata')->nullable()->after('status');
            }

            // Audit + soft deletes
            if (!Schema::hasColumn('discount_codes', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('metadata');
            }
            if (!Schema::hasColumn('discount_codes', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('discount_codes', 'deleted_at')) {
                $table->softDeletes();
            }

            // Indexes
            try {
                $table->index(['is_active', 'status']);
            } catch (\Throwable $e) {
            }
            try {
                $table->index(['starts_at', 'expires_at']);
            } catch (\Throwable $e) {
            }
            try {
                $table->index('created_by');
            } catch (\Throwable $e) {
            }
            try {
                $table->index('updated_by');
            } catch (\Throwable $e) {
            }

            // FKs
            try {
                $table->foreign('created_by', 'discount_codes_created_by_fk')->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
            } catch (\Throwable $e) {
            }
            try {
                $table->foreign('updated_by', 'discount_codes_updated_by_fk')->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
            } catch (\Throwable $e) {
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('discount_codes')) {
            return;
        }

        Schema::table('discount_codes', function (Blueprint $table): void {
            try {
                $table->dropForeign('discount_codes_created_by_fk');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign('discount_codes_updated_by_fk');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex(['is_active', 'status']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex(['starts_at', 'expires_at']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex(['created_by']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex(['updated_by']);
            } catch (\Throwable $e) {
            }

            foreach ([
                'description_lt',
                'description_en',
                'starts_at',
                'usage_limit_per_user',
                'is_active',
                'status',
                'metadata',
                'created_by',
                'updated_by',
            ] as $col) {
                if (Schema::hasColumn('discount_codes', $col)) {
                    $table->dropColumn($col);
                }
            }

            if (Schema::hasColumn('discount_codes', 'deleted_at')) {
                try {
                    $table->dropSoftDeletes();
                } catch (\Throwable $e) {
                }
            }
        });
    }
};
