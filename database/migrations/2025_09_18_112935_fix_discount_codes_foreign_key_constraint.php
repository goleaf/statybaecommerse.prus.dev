<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix discount_codes foreign key without dropping existing columns
        if (! Schema::hasTable('discount_codes')) {
            return;
        }

        Schema::table('discount_codes', function (Blueprint $table) {
            if (! Schema::hasColumn('discount_codes', 'discount_id')) {
                $table->unsignedBigInteger('discount_id')->nullable()->after('id');
            }

            // Add missing columns only if they don't exist (keep previously created structure intact)
            if (! Schema::hasColumn('discount_codes', 'name')) {
                $table->string('name')->nullable()->after('code');
            }
            if (! Schema::hasColumn('discount_codes', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('discount_codes', 'type')) {
                $table->string('type')->nullable()->after('description');
            }
            if (! Schema::hasColumn('discount_codes', 'value')) {
                $table->decimal('value', 10, 2)->default(0)->after('type');
            }
            if (! Schema::hasColumn('discount_codes', 'minimum_amount')) {
                $table->decimal('minimum_amount', 10, 2)->default(0)->after('value');
            }
            if (! Schema::hasColumn('discount_codes', 'maximum_discount')) {
                $table->decimal('maximum_discount', 10, 2)->nullable()->after('minimum_amount');
            }
            if (! Schema::hasColumn('discount_codes', 'usage_limit')) {
                $table->integer('usage_limit')->nullable()->after('maximum_discount');
            }
            if (! Schema::hasColumn('discount_codes', 'usage_limit_per_user')) {
                $table->integer('usage_limit_per_user')->nullable()->after('usage_limit');
            }
            if (! Schema::hasColumn('discount_codes', 'usage_count')) {
                $table->integer('usage_count')->default(0)->after('usage_limit_per_user');
            }
            if (! Schema::hasColumn('discount_codes', 'valid_from')) {
                $table->dateTime('valid_from')->nullable()->after('usage_count');
            }
            if (! Schema::hasColumn('discount_codes', 'valid_until')) {
                $table->dateTime('valid_until')->nullable()->after('valid_from');
            }
            if (! Schema::hasColumn('discount_codes', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('valid_until');
            }
            if (! Schema::hasColumn('discount_codes', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('discount_codes', 'is_auto_apply')) {
                $table->boolean('is_auto_apply')->default(false)->after('is_public');
            }
            if (! Schema::hasColumn('discount_codes', 'is_stackable')) {
                $table->boolean('is_stackable')->default(false)->after('is_auto_apply');
            }
            if (! Schema::hasColumn('discount_codes', 'is_first_time_only')) {
                $table->boolean('is_first_time_only')->default(false)->after('is_stackable');
            }
            if (! Schema::hasColumn('discount_codes', 'customer_group_id')) {
                $table->unsignedBigInteger('customer_group_id')->nullable()->after('is_first_time_only');
            }
            if (! Schema::hasColumn('discount_codes', 'status')) {
                $table->string('status')->default('inactive')->after('customer_group_id');
            }
            if (! Schema::hasColumn('discount_codes', 'metadata')) {
                $table->json('metadata')->nullable()->after('status');
            }
            if (! Schema::hasColumn('discount_codes', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('metadata');
            }
            if (! Schema::hasColumn('discount_codes', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }

            // Indexes
            if (! Schema::hasColumn('discount_codes', 'valid_from')) {
                // skip
            }
        });

        // Foreign keys are managed in earlier migrations; avoid duplicate FK additions here.
    }

    public function down(): void
    {
        // This migration is not reversible as it fixes a broken constraint
        // The original constraint was broken and needs to be fixed
    }
};
