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
        Schema::table('users', function (Blueprint $table) {
            // Personal Information
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('last_name');
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            }
            if (! Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 20)->nullable()->after('gender');
            }
            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 50)->default('Europe/Vilnius')->after('phone_number');
            }

            // Company Information
            if (! Schema::hasColumn('users', 'company')) {
                $table->string('company')->nullable()->after('timezone');
            }
            if (! Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable()->after('company');
            }

            // User Settings
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('job_title');
            }
            if (! Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('users', 'accepts_marketing')) {
                $table->boolean('accepts_marketing')->default(false)->after('is_verified');
            }
            if (! Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('accepts_marketing');
            }
            if (! Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('two_factor_enabled');
            }

            // Activity Tracking
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_admin');
            }
            if (! Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }

            // Profile
            if (! Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable()->after('last_login_ip');
            }
            if (! Schema::hasColumn('users', 'preferences')) {
                $table->json('preferences')->nullable()->after('avatar_url');
            }

            // Add indexes for better performance
            $table->index(['is_active', 'is_verified']);
            $table->index(['last_login_at']);
            $table->index(['created_at']);
            $table->index(['preferred_locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_verified']);
            $table->dropIndex(['last_login_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['preferred_locale']);

            $table->dropColumn([
                'first_name',
                'last_name',
                'date_of_birth',
                'gender',
                'phone_number',
                'timezone',
                'company',
                'job_title',
                'is_active',
                'is_verified',
                'accepts_marketing',
                'two_factor_enabled',
                'is_admin',
                'last_login_at',
                'last_login_ip',
                'avatar_url',
                'preferences',
            ]);
        });
    }
};
