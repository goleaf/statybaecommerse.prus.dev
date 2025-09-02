<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('sh_product_variants')) {
            return;
        }

        if (!Schema::hasColumn('sh_product_variants', 'status')) {
            Schema::table('sh_product_variants', function (Blueprint $table): void {
                $table->string('status', 32)->default('active')->index();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sh_product_variants')) {
            return;
        }

        if (Schema::hasColumn('sh_product_variants', 'status')) {
            Schema::table('sh_product_variants', function (Blueprint $table): void {
                $table->dropColumn('status');
            });
        }
    }
};
