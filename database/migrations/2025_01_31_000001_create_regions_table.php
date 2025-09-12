<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 10)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_default')->default(false);
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('regions')->onDelete('cascade');
            $table->integer('level')->default(0)->comment('Hierarchy level: 0=root, 1=state/province, 2=county, etc.');
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable()->comment('Additional region configuration');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_enabled', 'is_default']);
            $table->index(['code', 'is_enabled']);
            $table->index(['country_id', 'is_enabled']);
            $table->index(['zone_id', 'is_enabled']);
            $table->index(['parent_id', 'level']);
            $table->index(['level', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};


