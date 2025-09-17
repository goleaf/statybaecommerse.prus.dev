<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cca2', 2)->unique()->comment('ISO 3166-1 alpha-2 code');
            $table->string('cca3', 3)->unique()->comment('ISO 3166-1 alpha-3 code');
            $table->string('ccn3', 3)->nullable()->comment('ISO 3166-1 numeric code');
            $table->string('currency_code', 3)->nullable();
            $table->string('phone_code')->nullable();
            $table->string('flag')->nullable();
            $table->string('svg_flag')->nullable();
            $table->json('languages')->nullable();
            $table->json('timezones')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_enabled', 'sort_order']);
            $table->index(['cca2', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
