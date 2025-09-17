<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_id')->constrained('document_templates')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content'); // Generated HTML content
            $table->json('variables')->nullable(); // Variables used in this document
            $table->string('status')->default('draft'); // draft, published, archived
            $table->string('format')->default('html'); // html, pdf
            $table->string('file_path')->nullable(); // Path to generated PDF file
            $table->morphs('documentable'); // Related model (Order, Customer, etc.) - creates index automatically
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
