<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Export\ExportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exports', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('resource');
            $table->string('model');
            $table->string('format');
            $table->json('columns');
            $table->json('selection')->nullable();
            $table->json('filters')->nullable();
            $table->string('status')->default(ExportStatus::Pending->value);
            $table->string('path')->nullable();
            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('chunk_size')->default(config('export.chunk_size'));
            $table->timestamp('available_until')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};
