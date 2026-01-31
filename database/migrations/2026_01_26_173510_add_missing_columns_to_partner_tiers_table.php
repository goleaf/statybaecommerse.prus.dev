<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('partner_tiers', function (Blueprint $table) {
            if (! Schema::hasColumn('partner_tiers', 'code')) {
                $table->string('code')->unique()->after('name');
            }
            if (! Schema::hasColumn('partner_tiers', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('priority');
            }
            if (! Schema::hasColumn('partner_tiers', 'discount_rate')) {
                $table->decimal('discount_rate', 5, 4)->nullable()->after('default_discount_pct');
            }
            if (! Schema::hasColumn('partner_tiers', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 4)->nullable()->after('discount_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_tiers', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_enabled', 'discount_rate', 'commission_rate']);
        });
    }
};
