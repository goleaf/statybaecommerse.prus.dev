<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('partners')) {
            return;
        }

        Schema::table('partners', function (Blueprint $table) {
            if (! Schema::hasColumn('partners', 'tier_id')) {
                $table->unsignedBigInteger('tier_id')->nullable()->after('code');
            }

            if (! Schema::hasColumn('partners', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        // No-op for SQLite tests
    }
};

