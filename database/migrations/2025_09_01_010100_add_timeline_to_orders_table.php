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
				if (!Schema::hasColumn($tableName, 'timeline')) {
					$table->json('timeline')->nullable()->after('transactions');
				}
			});
		}
	}

	public function down(): void
	{
		$tables = array_filter(['sh_orders', 'orders'], fn($t) => Schema::hasTable($t));
		foreach ($tables as $tableName) {
			Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
				if (Schema::hasColumn($tableName, 'timeline')) {
					$table->dropColumn('timeline');
				}
			});
		}
	}
};
