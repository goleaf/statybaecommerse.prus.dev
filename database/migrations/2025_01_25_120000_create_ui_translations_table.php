<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ui_translations')) {
            Schema::create('ui_translations', function (Blueprint $table): void {
                $table->id();
                $table->string('key')->index();
                $table->string('locale', 10)->index();
                $table->text('value');
                $table->string('group')->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamps();

                // Ensure unique key-locale combinations
                $table->unique(['key', 'locale']);

                // Composite indexes for common queries
                $table->index(['group', 'locale']);
                $table->index(['key', 'group']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ui_translations');
    }
};
