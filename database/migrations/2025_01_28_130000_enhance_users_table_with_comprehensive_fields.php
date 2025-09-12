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
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->date('date_of_birth')->nullable()->after('last_name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->string('phone_number', 20)->nullable()->after('gender');
            $table->string('timezone', 50)->default('Europe/Vilnius')->after('phone_number');
            
            // Company Information
            $table->string('company')->nullable()->after('timezone');
            $table->string('job_title')->nullable()->after('company');
            
            // User Settings
            $table->boolean('is_active')->default(true)->after('job_title');
            $table->boolean('is_verified')->default(false)->after('is_active');
            $table->boolean('accepts_marketing')->default(false)->after('is_verified');
            $table->boolean('two_factor_enabled')->default(false)->after('accepts_marketing');
            $table->boolean('is_admin')->default(false)->after('two_factor_enabled');
            
            // Activity Tracking
            $table->timestamp('last_login_at')->nullable()->after('is_admin');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            
            // Profile
            $table->string('avatar_url')->nullable()->after('last_login_ip');
            $table->json('preferences')->nullable()->after('avatar_url');
            
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

