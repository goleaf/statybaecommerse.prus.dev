<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sh_orders')) {
            return;
        }
        Schema::table('sh_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('sh_orders', 'timeline')) {
                $table->json('timeline')->nullable()->after('transactions');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sh_orders')) {
            return;
        }
        Schema::table('sh_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('sh_orders', 'timeline')) {
                $table->dropColumn('timeline');
            }
        });
    }
};
