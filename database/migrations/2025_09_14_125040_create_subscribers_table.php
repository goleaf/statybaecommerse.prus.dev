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
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->json('interests')->nullable(); // Store user interests/categories
            $table->string('source')->default('website'); // How they subscribed
            $table->string('status')->default('active'); // active, inactive, unsubscribed
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamp('last_email_sent_at')->nullable();
            $table->integer('email_count')->default(0);
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamps();

            // Indexes
            $table->index(['status', 'subscribed_at']);
            $table->index('email');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
