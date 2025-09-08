<?php declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->artisan('migrate', ['--force' => true]);
    if (! Schema::hasTable('orders')) {
        Schema::create('orders', function ($table) {
            $table->id();
            $table->string('number')->nullable();
            $table->timestamps();
        });
    }
    if (! Schema::hasTable('discount_redemptions')) {
        Schema::create('discount_redemptions', function ($table) { $table->id(); $table->unsignedBigInteger('discount_id'); $table->unsignedBigInteger('order_id'); $table->decimal('amount', 12, 2)->default(0); $table->timestamps(); });
    }
    if (! Schema::hasTable('discounts')) {
        Schema::create('discounts', function ($table) { $table->id(); $table->string('type')->nullable(); $table->string('code')->nullable(); $table->timestamps(); });
    }
});

it('shows confirmation page for order number', function (): void {
    $id = DB::table('orders')->insertGetId(['number' => 'ABC123', 'created_at' => now(), 'updated_at' => now()]);

    actingAs(User::factory()->create());

    $resp = $this->get('/en/order/confirmed/ABC123');
    $resp->assertOk();
});


