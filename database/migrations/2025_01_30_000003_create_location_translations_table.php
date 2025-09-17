<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('location_translations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->string('locale', 2);
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['location_id', 'locale']);
            $table->unique(['locale', 'slug']);
            $table->index(['locale', 'location_id']);
        });

        if (Schema::hasTable('locations')) {
            Schema::table('location_translations', function (Blueprint $table) {
                $table->foreign('location_id')->references('id')->on('locations')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('location_translations');
    }
};
