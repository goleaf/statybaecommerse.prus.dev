<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legals', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('type')->default('legal_document'); // privacy_policy, terms_of_use, refund_policy, etc.
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_required')->default(false); // Required legal documents
            $table->integer('sort_order')->default(0);
            $table->json('meta_data')->nullable(); // Additional metadata
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_enabled']);
            $table->index(['is_required', 'is_enabled']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legals');
    }
};
