<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations with defensive guards to keep deployments idempotent.
     */
    public function up(): void
    {
        if (Schema::hasTable('idempotency_keys')) {
            // The table already exists so bail out early to avoid duplicate creation attempts.
            return;
        }

        Schema::create('idempotency_keys', static function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('request_hash');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->json('response_body')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations while keeping rollbacks safe across environments.
     */
    public function down(): void
    {
        if (! Schema::hasTable('idempotency_keys')) {
            // Nothing to drop when the table never existed.
            return;
        }

        Schema::dropIfExists('idempotency_keys');
    }
};
