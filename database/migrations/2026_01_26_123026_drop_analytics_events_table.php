<?php

declare(strict_types=1);

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
        Schema::dropIfExists('analytics_events');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->index();
            $table->string('session_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('url')->nullable();
            $table->string('referrer')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('country_code')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('screen_resolution')->nullable();
            $table->nullableMorphs('trackable');
            $table->decimal('value', 12, 2)->nullable();
            $table->string('currency')->nullable();
            $table->json('properties')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }
};