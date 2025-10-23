<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('api_keys')) {
            return;
        }

        Schema::create('api_keys', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 128)->unique();
            $table->string('name');
            $table->string('secret', 128)->nullable();
            $table->json('scopes')->nullable();
            $table->json('permissions')->nullable();
            $table->unsignedInteger('rate_limit')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('rate_limit');
            $table->index('is_active');
            $table->index('last_used_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
