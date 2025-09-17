<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 10)->unique();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_default')->default(false);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
            $table->decimal('tax_rate', 5, 4)->default(0.0)->comment('Tax rate as percentage (e.g., 21.0000 for 21%)');
            $table->decimal('shipping_rate', 10, 2)->default(0.0)->comment('Base shipping rate');
            $table->json('metadata')->nullable()->comment('Additional zone configuration');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_enabled', 'is_default']);
            $table->index(['code', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
