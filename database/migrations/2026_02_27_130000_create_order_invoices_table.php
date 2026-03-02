<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->string('external_invoice_id')->nullable();
            $table->string('invoice_series')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('full_number')->nullable();
            $table->string('invoice_type')->nullable();
            $table->string('status', 32)->default('pending');
            $table->boolean('is_current')->default(true);
            $table->string('generation_mode', 32)->default('auto');
            $table->json('provider_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'is_current']);
            $table->index(['order_id', 'status']);
            $table->index('external_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_invoices');
    }
};
