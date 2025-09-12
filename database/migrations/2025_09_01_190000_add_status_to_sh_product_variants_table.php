<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_variants')) {
            return;
        }

        if (! Schema::hasColumn('product_variants', 'status')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->string('status', 32)->default('active')->index();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_variants')) {
            return;
        }

        if (Schema::hasColumn('product_variants', 'status')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->dropColumn('status');
            });
        }
    }
};
