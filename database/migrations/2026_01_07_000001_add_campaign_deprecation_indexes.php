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
        // Add soft delete support for campaign tables to enable graceful deprecation
        if (Schema::hasTable('discount_campaigns')) {
            Schema::table('discount_campaigns', function (Blueprint $table) {
                if (! Schema::hasColumn('discount_campaigns', 'deprecated_at')) {
                    $table->timestamp('deprecated_at')->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('email_campaigns')) {
            Schema::table('email_campaigns', function (Blueprint $table) {
                if (! Schema::hasColumn('email_campaigns', 'deprecated_at')) {
                    $table->timestamp('deprecated_at')->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('referral_campaigns')) {
            Schema::table('referral_campaigns', function (Blueprint $table) {
                if (! Schema::hasColumn('referral_campaigns', 'deprecated_at')) {
                    $table->timestamp('deprecated_at')->nullable()->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['discount_campaigns', 'email_campaigns', 'referral_campaigns'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deprecated_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('deprecated_at');
                });
            }
        }
    }
};
