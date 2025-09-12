<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('type'); // percentage, fixed, free_shipping, bogo
            $table->decimal('value', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->decimal('minimum_amount', 10, 2)->nullable();
            $table->decimal('maximum_amount', 10, 2)->nullable();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            // Optional fields referenced by model
            $table->string('status')->nullable();
            $table->json('scope')->nullable();
            $table->string('stacking_policy')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('exclusive')->default(false);
            $table->boolean('applies_to_shipping')->default(false);
            $table->boolean('free_shipping')->default(false);
            $table->boolean('first_order_only')->default(false);
            $table->unsignedInteger('per_customer_limit')->nullable();
            $table->unsignedInteger('per_code_limit')->nullable();
            $table->unsignedInteger('per_day_limit')->nullable();
            $table->json('channel_restrictions')->nullable();
            $table->json('currency_restrictions')->nullable();
            $table->string('weekday_mask')->nullable();
            $table->json('time_window')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
