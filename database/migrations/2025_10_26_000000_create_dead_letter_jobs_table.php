<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dead_letter_jobs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('connection')->index();
            $table->string('queue')->index();
            $table->string('job');
            $table->unsignedInteger('attempts')->default(0);
            $table->longText('payload');
            $table->text('exception')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('failed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dead_letter_jobs');
    }
};
