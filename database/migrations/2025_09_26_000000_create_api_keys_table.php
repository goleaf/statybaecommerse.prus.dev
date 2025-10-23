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
            $table->string('key', 64)->unique();
            $table->string('name')->index();
            $table->json('scopes');
            $table->unsignedInteger('rate_limit')->nullable()->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamp('last_used_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
