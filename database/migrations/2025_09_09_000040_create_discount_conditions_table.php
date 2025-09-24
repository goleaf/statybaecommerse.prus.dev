<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('discount_conditions')) {
            return;
        }

        Schema::create('discount_conditions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->string('type');
            $table->string('operator');
            $table->json('value')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['discount_id']);
            $table->index(['type']);
            $table->index(['operator']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_conditions');
    }
};
