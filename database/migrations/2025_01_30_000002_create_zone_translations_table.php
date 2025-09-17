<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('zone_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->string('locale', 2);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['zone_id', 'locale']);
            $table->index(['locale', 'zone_id']);
        });

        if (Schema::hasTable('zones')) {
            Schema::table('zone_translations', function (Blueprint $table) {
                $table->foreign('zone_id')->references('id')->on('zones')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_translations');
    }
};
