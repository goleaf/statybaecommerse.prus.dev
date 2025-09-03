<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tableName = 'reviews';

        if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'locale')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('locale', 10)->nullable()->after('approved');
                $table->index('locale');
            });
        }
    }

    public function down(): void
    {
        $tableName = 'reviews';

        if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'locale')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropIndex([$table->getTable() . '_locale_index']);
                $table->dropColumn('locale');
            });
        }
    }
};
