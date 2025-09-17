<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'preferred_locale')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('preferred_locale', 10)->nullable()->after('email_verified_at')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'preferred_locale')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('preferred_locale');
            });
        }
    }
};
