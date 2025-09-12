<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sh_order_shippings')) {
            Schema::create('sh_order_shippings', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->string('carrier_name')->nullable();
                $table->string('tracking_number')->nullable();
                $table->string('tracking_url')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sh_order_shippings')) {
            Schema::dropIfExists('sh_order_shippings');
        }
    }
};
