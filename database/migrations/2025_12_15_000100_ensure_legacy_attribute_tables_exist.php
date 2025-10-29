<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sh_attributes')) {
            Schema::create('sh_attributes', function (Blueprint $table): void {
                // Legacy attribute snapshot kept minimal for test-only cascades.
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sh_attribute_values')) {
            Schema::create('sh_attribute_values', function (Blueprint $table): void {
                // Table mirrors the essential columns used by downstream foreign keys.
                $table->id();
                $table->unsignedBigInteger('attribute_id');
                $table->string('value');
                $table->timestamps();

                if (Schema::hasTable('sh_attributes')) {
                    $table->foreign('attribute_id')
                        ->references('id')
                        ->on('sh_attributes')
                        ->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sh_attribute_values');
        Schema::dropIfExists('sh_attributes');
    }
};
