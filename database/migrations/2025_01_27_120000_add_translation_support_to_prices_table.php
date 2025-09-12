<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            // Add cost_amount column if it doesn't exist
            if (!Schema::hasColumn('prices', 'cost_amount')) {
                $table->decimal('cost_amount', 12, 4)->nullable()->after('compare_amount');
            }
            
            // Add metadata column if it doesn't exist
            if (!Schema::hasColumn('prices', 'metadata')) {
                $table->json('metadata')->nullable()->after('is_enabled');
            }
        });

        // Create price_translations table
        Schema::create('price_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_id')->constrained()->onDelete('cascade');
            $table->string('locale', 5)->index();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['price_id', 'locale']);
            $table->index(['locale', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_translations');
        
        Schema::table('prices', function (Blueprint $table) {
            if (Schema::hasColumn('prices', 'cost_amount')) {
                $table->dropColumn('cost_amount');
            }
            if (Schema::hasColumn('prices', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
