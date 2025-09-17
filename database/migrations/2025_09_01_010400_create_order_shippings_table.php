<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('sh_order_shippings')) {
            Schema::create('sh_order_shippings', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->string('carrier_name')->nullable();
                $table->string('tracking_number')->nullable();
                $table->string('tracking_url')->nullable();
                $table->timestamps();

                $table->index(['order_id']);
            });
            Schema::table('sh_order_shippings', function (Blueprint $table) {
                if (Schema::hasTable('sh_orders')) {
                    $table->foreign('order_id')->references('id')->on('sh_orders')->cascadeOnUpdate()->cascadeOnDelete();
                } elseif (Schema::hasTable('orders')) {
                    $table->foreign('order_id')->references('id')->on('orders')->cascadeOnUpdate()->cascadeOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sh_order_shippings');
    }
};
