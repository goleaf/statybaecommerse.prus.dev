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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->index();
            $table->string('session_id')->index();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('url')->nullable();
            $table->string('referrer')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('device_type')->nullable()->index();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('screen_resolution')->nullable();
            $table->string('trackable_type')->nullable();
            $table->unsignedBigInteger('trackable_id')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->json('properties')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['event_type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['session_id', 'created_at']);
            $table->index(['trackable_type', 'trackable_id']);
            $table->index(['device_type', 'created_at']);
            $table->index(['browser', 'created_at']);
            $table->index(['country_code', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};

