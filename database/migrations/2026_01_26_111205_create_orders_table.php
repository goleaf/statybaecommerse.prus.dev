<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending')->index();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency')->default('EUR');
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            // Relationships references - simplified for now as constrained dependencies might not exist in factory test context unless full migration runs
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->unsignedBigInteger('shipping_option_id')->nullable();
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            
            $table->string('payment_status')->nullable();
            $table->string('payment_state')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for optimizing searches
            $table->index('number');
            $table->index('user_id');
            $table->index('created_at', 'orders_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
