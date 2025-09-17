<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = array_filter(['sh_orders', 'orders'], fn($t) => Schema::hasTable($t));
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (!Schema::hasColumn($tableName, 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('payment_method_id');
                }
                if (!Schema::hasColumn($tableName, 'payment_status')) {
                    $table->string('payment_status', 32)->default('pending')->after('payment_method');
                }
                if (!Schema::hasColumn($tableName, 'transactions')) {
                    $table->json('transactions')->nullable()->after('payment_status');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = array_filter(['sh_orders', 'orders'], fn($t) => Schema::hasTable($t));
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                foreach (['transactions', 'payment_status', 'payment_method'] as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
