<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table): void {
            $table->id();
            $table->morphs('auditable');
            $table->string('event');
            $table->nullableMorphs('actor');
            $table->text('reason')->nullable();
            $table->string('request_id');
            $table->json('diff');
            $table->timestamps();

            $table->index('event');
            $table->index('created_at');
            $table->index('request_id');
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['actor_type', 'actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
