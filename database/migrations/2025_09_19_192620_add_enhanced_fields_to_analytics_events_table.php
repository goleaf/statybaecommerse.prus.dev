<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->string('event_name')->nullable()->after('id');
            $table->text('description')->nullable()->after('event_name');
            $table->boolean('is_important')->default(false)->after('description');
            $table->boolean('is_conversion')->default(false)->after('is_important');
            $table->decimal('conversion_value', 10, 2)->nullable()->after('is_conversion');
            $table->string('conversion_currency', 3)->default('EUR')->after('conversion_value');
            $table->text('notes')->nullable()->after('conversion_currency');
            $table->string('user_name')->nullable()->after('notes');
            $table->string('user_email')->nullable()->after('user_name');
            $table->json('event_data')->nullable()->after('user_email');
            $table->string('utm_source')->nullable()->after('event_data');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('utm_term')->nullable()->after('utm_campaign');
            $table->string('utm_content')->nullable()->after('utm_term');
            $table->string('referrer_url')->nullable()->after('utm_content');
            $table->string('country')->nullable()->after('referrer_url');
            $table->string('city')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropColumn([
                'event_name', 'description', 'is_important', 'is_conversion',
                'conversion_value', 'conversion_currency', 'notes', 'user_name',
                'user_email', 'event_data', 'utm_source', 'utm_medium',
                'utm_campaign', 'utm_term', 'utm_content', 'referrer_url',
                'country', 'city'
            ]);
        });
    }
};
