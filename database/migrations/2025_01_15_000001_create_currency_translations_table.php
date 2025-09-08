<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('currency_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->string('locale', 2);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['currency_id', 'locale']);
            $table->index(['locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_translations');
    }
};
