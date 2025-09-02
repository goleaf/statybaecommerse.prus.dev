<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content'); // HTML content with variables
            $table->json('variables')->nullable(); // Available variables for this template
            $table->string('type')->default('document'); // document, invoice, receipt, contract, etc.
            $table->string('category')->nullable(); // sales, marketing, legal, etc.
            $table->json('settings')->nullable(); // Print settings, CSS, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
