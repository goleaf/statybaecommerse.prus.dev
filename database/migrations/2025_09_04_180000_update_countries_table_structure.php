<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            // Add missing columns
            if (!Schema::hasColumn('countries', 'phone_calling_code')) {
                $table->string('phone_calling_code')->nullable()->after('phone_code');
            }
            if (!Schema::hasColumn('countries', 'region')) {
                $table->string('region')->nullable()->after('flag');
            }
            if (!Schema::hasColumn('countries', 'subregion')) {
                $table->string('subregion')->nullable()->after('region');
            }
            if (!Schema::hasColumn('countries', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('subregion');
            }
            if (!Schema::hasColumn('countries', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('countries', 'currencies')) {
                $table->json('currencies')->nullable()->after('longitude');
            }

            // Make name nullable since we'll use translations
            if (Schema::hasColumn('countries', 'name')) {
                $table->string('name')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn([
                'phone_calling_code',
                'region',
                'subregion',
                'latitude',
                'longitude',
                'currencies'
            ]);

            $table->string('name')->nullable(false)->change();
        });
    }
};
