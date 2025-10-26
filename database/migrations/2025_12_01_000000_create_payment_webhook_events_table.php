<?php

declare(strict_types=1);

use App\Enums\OrderPaymentState;
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
        // Add the payment_state column if it is missing so legacy imports gain
        // the explicit lifecycle the webhook processor relies on.
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'payment_state')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('payment_state')->default(OrderPaymentState::CREATED->value)->after('payment_status');
            });
        }

        // Create the idempotency log for payment webhooks to ensure providers
        // can safely retry without triggering duplicate side effects.
        if (! Schema::hasTable('payment_webhook_events')) {
            Schema::create('payment_webhook_events', function (Blueprint $table): void {
                $table->id();
                $table->string('provider');
                $table->string('event_id');
                $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
                $table->string('status');
                $table->json('payload')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
                $table->unique(['provider', 'event_id'], 'payment_webhook_events_provider_event_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payment_webhook_events')) {
            Schema::drop('payment_webhook_events');
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'payment_state')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('payment_state');
            });
        }
    }
};
