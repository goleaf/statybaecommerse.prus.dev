<?php

declare(strict_types=1);

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
        if (! Schema::hasTable('shipping_options')) {
            Schema::create('shipping_options', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('description')->nullable();
                $table->string('carrier_name')->nullable();
                $table->string('service_type')->nullable();
                $table->decimal('price', 12, 2)->nullable();
                $table->string('currency_code', 3)->default('EUR');
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('city_id')->nullable();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_default')->default(false);
                $table->integer('sort_order')->default(0);
                $table->integer('min_weight')->nullable();
                $table->integer('max_weight')->nullable();
                $table->decimal('min_order_amount', 12, 2)->nullable();
                $table->decimal('max_order_amount', 12, 2)->nullable();
                $table->integer('estimated_days_min')->nullable();
                $table->integer('estimated_days_max')->nullable();
                $table->json('metadata')->nullable();
                $table->json('shipping_matrix')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_enabled', 'sort_order']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_options');
    }
};
