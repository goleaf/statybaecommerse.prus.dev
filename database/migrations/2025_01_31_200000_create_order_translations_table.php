<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_translations', function (Blueprint $table) {
            $table->id();
            // Defer FK to orders; ensure creation order stability on MySQL
            $table->unsignedBigInteger('order_id');
            $table->string('locale', 8);
            $table->text('notes')->nullable();
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'locale']);
            $table->index(['locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_translations');
    }
};
