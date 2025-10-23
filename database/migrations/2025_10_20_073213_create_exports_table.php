<?php

declare(strict_types=1);

use App\Enums\ExportFormat;
use App\Enums\ExportStatus;
use App\Enums\ExportType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('requested_by')->constrained('users');
            $table->string('type');
            $table->string('format');
            $table->string('status')->default(ExportStatus::PENDING->value);
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('locale')->default(config('app.locale'));
            $table->string('timezone')->default(config('app.timezone'));
            $table->unsignedInteger('total_rows')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};
