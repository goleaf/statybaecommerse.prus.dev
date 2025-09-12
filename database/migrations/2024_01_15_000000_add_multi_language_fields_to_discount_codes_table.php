<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            // Add multi-language description fields
            $table->text('description_lt')->nullable()->after('code');
            $table->text('description_en')->nullable()->after('description_lt');
            
            // Add new fields for enhanced functionality
            $table->timestamp('starts_at')->nullable()->after('description_en');
            $table->integer('usage_limit')->nullable()->change();
            $table->integer('usage_limit_per_user')->nullable()->after('usage_limit');
            $table->boolean('is_active')->default(true)->after('usage_limit_per_user');
            $table->string('status')->default('active')->after('is_active');
            $table->json('metadata')->nullable()->after('status');
            
            // Add tracking fields
            $table->unsignedBigInteger('created_by')->nullable()->after('metadata');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            
            // Add soft deletes
            $table->softDeletes();
            
            // Add indexes
            $table->index(['is_active', 'status']);
            $table->index(['starts_at', 'expires_at']);
            $table->index('created_by');
            $table->index('updated_by');
            
            // Add foreign key constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropIndex(['is_active', 'status']);
            $table->dropIndex(['starts_at', 'expires_at']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'description_lt',
                'description_en',
                'starts_at',
                'usage_limit_per_user',
                'is_active',
                'status',
                'metadata',
                'created_by',
                'updated_by'
            ]);
        });
    }
};
