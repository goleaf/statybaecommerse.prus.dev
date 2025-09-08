<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('zones')) {
            Schema::table('zones', function (Blueprint $table) {
                if (! Schema::hasColumn('zones', 'description')) {
                    $table->json('description')->nullable()->after('slug');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('zones')) {
            Schema::table('zones', function (Blueprint $table) {
                if (Schema::hasColumn('zones', 'description')) {
                    $table->dropColumn('description');
                }
            });
        }
    }
};

