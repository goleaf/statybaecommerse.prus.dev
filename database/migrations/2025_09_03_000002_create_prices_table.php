<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('priceable_id');
            $table->string('priceable_type');
            $table->unsignedBigInteger('currency_id');
            $table->decimal('amount', 12, 4);
            $table->decimal('compare_amount', 12, 4)->nullable();
            $table->enum('type', ['retail', 'wholesale', 'special', 'sale'])->default('retail');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');

            $table->index(['priceable_id', 'priceable_type']);
            $table->index(['currency_id', 'is_enabled']);
            $table->index(['priceable_id', 'priceable_type', 'currency_id', 'is_enabled'], 'prices_composite_idx');
            $table->index(['type', 'is_enabled']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
