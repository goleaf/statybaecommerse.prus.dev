<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('channels')) {
            Schema::create('channels', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('timezone')->nullable();
                $table->string('url')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_default')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_enabled', 'is_default']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
