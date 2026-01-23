<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop campaign conversion tables in correct order (child tables first)
        Schema::dropIfExists('campaign_conversion_translations');
        Schema::dropIfExists('campaign_conversions');
    }

    public function down(): void
    {
        // Recreate tables if needed (basic structure only)
        Schema::create('campaign_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('discount_campaigns')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('conversion_type');
            $table->decimal('conversion_value', 10, 2);
            $table->string('status');
            $table->timestamp('converted_at');
            $table->timestamps();
        });

        Schema::create('campaign_conversion_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_conversion_id')->constrained('campaign_conversions')->onDelete('cascade');
            $table->string('locale');
            $table->string('conversion_type_label')->nullable();
            $table->string('status_label')->nullable();
            $table->text('notes')->nullable();
            $table->text('custom_data')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            $table->unique(['campaign_conversion_id', 'locale'], 'campaign_conversion_locale_unique');
            $table->index('locale');
        });
    }
};
