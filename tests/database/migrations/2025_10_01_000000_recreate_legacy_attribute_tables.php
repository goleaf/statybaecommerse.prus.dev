<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recreate legacy attribute tables that older foreign keys still reference during tests.
        if (! Schema::hasTable('sh_attributes')) {
            Schema::create('sh_attributes', function (Blueprint $table): void {
                // Provide minimal columns required for foreign key integrity while keeping schema lean for SQLite tests.
                $table->id();
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sh_attribute_values')) {
            Schema::create('sh_attribute_values', function (Blueprint $table): void {
                // Maintain the attribute reference so drop statements against products respect the foreign key definition.
                $table->id();
                $table->unsignedBigInteger('attribute_id')->nullable();
                $table->string('value')->nullable();
                $table->timestamps();

                $table->foreign('attribute_id')
                    ->references('id')
                    ->on('sh_attributes')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('sh_product_attributes')) {
            Schema::create('sh_product_attributes', function (Blueprint $table): void {
                // Create a lightweight pivot so schema drops do not fail because of missing intermediate relations.
                $table->id();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('attribute_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sh_product_variant_attributes')) {
            Schema::create('sh_product_variant_attributes', function (Blueprint $table): void {
                // Cover variant level lookups with nullable references to keep migrations reversible in SQLite.
                $table->id();
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->unsignedBigInteger('attribute_value_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Remove recreated legacy tables so test teardown mirrors production schema expectations.
        Schema::dropIfExists('sh_product_variant_attributes');
        Schema::dropIfExists('sh_product_attributes');
        Schema::dropIfExists('sh_attribute_values');
        Schema::dropIfExists('sh_attributes');
    }
};
