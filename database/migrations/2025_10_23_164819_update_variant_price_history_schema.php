<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('variant_price_history') && ! Schema::hasTable('variant_price_histories')) {
            Schema::rename('variant_price_history', 'variant_price_histories');
        }

        if (! Schema::hasTable('variant_price_histories')) {
            return;
        }

        if (! Schema::hasColumn('variant_price_histories', 'reason')) {
            Schema::table('variant_price_histories', function (Blueprint $table): void {
                $table->string('reason')->nullable();
            });

            if (Schema::hasColumn('variant_price_histories', 'change_reason')) {
                DB::table('variant_price_histories')
                    ->whereNull('reason')
                    ->update(['reason' => DB::raw('change_reason')]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('variant_price_histories')) {
            return;
        }

        if (! Schema::hasColumn('variant_price_histories', 'change_reason')) {
            Schema::table('variant_price_histories', function (Blueprint $table): void {
                $table->string('change_reason')->nullable();
            });
        }

        if (Schema::hasColumn('variant_price_histories', 'reason')) {
            DB::table('variant_price_histories')
                ->whereNull('change_reason')
                ->update(['change_reason' => DB::raw('reason')]);
        }

        if (Schema::hasTable('variant_price_histories') && ! Schema::hasTable('variant_price_history')) {
            Schema::rename('variant_price_histories', 'variant_price_history');
        }
    }
};
