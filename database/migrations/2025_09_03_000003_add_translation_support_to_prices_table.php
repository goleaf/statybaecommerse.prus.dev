<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->decimal('cost_amount', 15, 2)->nullable()->after('compare_amount');
            $table->json('metadata')->nullable()->after('is_enabled');
        });

        Schema::create('price_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('locale', 5)->index();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['price_id', 'locale'], 'price_translations_price_locale_unique');
            $table->index(['locale', 'name'], 'price_translations_locale_name_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_translations');

        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn('cost_amount');
            $table->dropColumn('metadata');
        });
    }
};
