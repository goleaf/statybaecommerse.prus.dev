<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('discount_codes')) {
            Schema::create('discount_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('type');
                $table->decimal('value', 10, 2)->default(0);
                $table->decimal('minimum_amount', 10, 2)->default(0);
                $table->decimal('maximum_discount', 10, 2)->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('usage_limit_per_user')->nullable();
                $table->integer('usage_count')->default(0);
                $table->dateTime('valid_from')->nullable();
                $table->dateTime('valid_until')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_public')->default(false);
                $table->boolean('is_auto_apply')->default(false);
                $table->boolean('is_stackable')->default(false);
                $table->boolean('is_first_time_only')->default(false);
                $table->unsignedBigInteger('customer_group_id')->nullable();
                $table->string('status')->default('inactive');
                $table->json('metadata')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['is_active', 'status']);
                $table->index(['customer_group_id']);
                $table->index(['valid_from', 'valid_until']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};

