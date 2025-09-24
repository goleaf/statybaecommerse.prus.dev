<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('collection_rules')) {
            Schema::create('collection_rules', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
                $table->string('field');
                $table->string('operator');
                $table->string('value')->nullable();
                $table->integer('position')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['collection_id']);
                $table->index(['is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_rules');
    }
};
