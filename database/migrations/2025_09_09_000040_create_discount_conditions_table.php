<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

        if (! Schema::hasTable('discount_condition_translations')) {
            // Provision the translation table alongside the primary resource when installing from scratch.
            Schema::create('discount_condition_translations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('discount_condition_id')->constrained()->cascadeOnDelete();
                $table->string('locale', 5);
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['discount_condition_id', 'locale']);
                $table->index(['locale']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('discount_condition_translations')) {
            // Drop the translation table during rollbacks so schema stays in sync.
            Schema::dropIfExists('discount_condition_translations');
        }

        Schema::dropIfExists('discount_conditions');
    }
};
