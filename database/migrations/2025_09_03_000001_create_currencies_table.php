<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique();
            $table->string('symbol', 10)->nullable();
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->tinyInteger('decimal_places')->default(2);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code']);
            $table->index(['is_enabled', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
