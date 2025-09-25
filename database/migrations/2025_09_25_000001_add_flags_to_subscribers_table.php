<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscribers')) {
            Schema::table('subscribers', function (Blueprint $table) {
                if (! Schema::hasColumn('subscribers', 'is_verified')) {
                    $table->boolean('is_verified')->default(false)->after('status');
                }
                if (! Schema::hasColumn('subscribers', 'accepts_marketing')) {
                    $table->boolean('accepts_marketing')->default(true)->after('is_verified');
                }
                if (! Schema::hasColumn('subscribers', 'newsletter_subscription')) {
                    $table->boolean('newsletter_subscription')->default(true)->after('accepts_marketing');
                }
                if (! Schema::hasColumn('subscribers', 'unsubscribe_reason')) {
                    $table->text('unsubscribe_reason')->nullable()->after('unsubscribed_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscribers')) {
            Schema::table('subscribers', function (Blueprint $table) {
                if (Schema::hasColumn('subscribers', 'is_verified')) {
                    $table->dropColumn('is_verified');
                }
                if (Schema::hasColumn('subscribers', 'accepts_marketing')) {
                    $table->dropColumn('accepts_marketing');
                }
                if (Schema::hasColumn('subscribers', 'newsletter_subscription')) {
                    $table->dropColumn('newsletter_subscription');
                }
                if (Schema::hasColumn('subscribers', 'unsubscribe_reason')) {
                    $table->dropColumn('unsubscribe_reason');
                }
            });
        }
    }
};
