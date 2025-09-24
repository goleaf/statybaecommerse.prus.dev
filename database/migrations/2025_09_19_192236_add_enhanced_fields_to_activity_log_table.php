<?php

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
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
            // Add enhanced fields if they don't exist
            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('properties');
            }

            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }

            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'device_type')) {
                $table->string('device_type', 50)->nullable()->after('user_agent');
            }

            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'browser')) {
                $table->string('browser', 100)->nullable()->after('device_type');
            }

            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'os')) {
                $table->string('os', 100)->nullable()->after('browser');
            }

            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'country')) {
                $table->string('country', 100)->nullable()->after('os');
            }

            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'is_important')) {
                $table->boolean('is_important')->default(false)->after('country');
            }

            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'is_system')) {
                $table->boolean('is_system')->default(false)->after('is_important');
            }

            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'severity')) {
                $table->string('severity', 20)->nullable()->after('is_system');
            }

            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'category')) {
                $table->string('category', 100)->nullable()->after('severity');
            }

            if (! Schema::connection(config('activitylog.database_connection'))->hasColumn(config('activitylog.table_name'), 'notes')) {
                $table->text('notes')->nullable()->after('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->dropColumn([
                'ip_address',
                'user_agent',
                'device_type',
                'browser',
                'os',
                'country',
                'is_important',
                'is_system',
                'severity',
                'category',
                'notes',
            ]);
        });
    }
};
