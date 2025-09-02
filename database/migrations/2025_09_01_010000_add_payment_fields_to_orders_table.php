<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('sh_orders')) {
            return;
        }

        Schema::table('sh_orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('sh_orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_method_id');
            }
            if (!Schema::hasColumn('sh_orders', 'payment_status')) {
                $table->string('payment_status', 32)->default('pending')->after('payment_method');
            }
            if (!Schema::hasColumn('sh_orders', 'transactions')) {
                $table->json('transactions')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('sh_orders')) {
            return;
        }

        Schema::table('sh_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('sh_orders', 'transactions')) {
                $table->dropColumn('transactions');
            }
            if (Schema::hasColumn('sh_orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('sh_orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
