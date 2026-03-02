<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Config::set('invoices.enabled', true);
    Config::set('invoices.base_url', 'https://saskaita.vercel.app');
    Config::set('invoices.api_token', 'test-api-token');
    Config::set('invoices.auth_bearer', '');
    Config::set('invoices.timeout_seconds', 5);
    Config::set('invoices.retry_times', 1);
    Config::set('invoices.retry_sleep_ms', 50);

    Storage::fake('secure-media');
});

it('fails when order-id option is invalid', function (): void {
    $this->artisan('orders:invoices:backfill --order-id=abc')
        ->assertExitCode(1);
});

it('fails when allow-unpaid option is used without order-id', function (): void {
    $this->artisan('orders:invoices:backfill --allow-unpaid')
        ->assertExitCode(1);
});

it('can generate invoice for one unpaid order when allow-unpaid is set', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'        => $user->getKey(),
        'number'         => 'ORD-INV-BACKFILL-ONE-1',
        'payment_status' => 'pending',
        'total'          => 121.00,
    ]);

    $order->items()->create([
        'name'       => 'Backfill command item',
        'sku'        => 'INV-BACKFILL-ITEM-1',
        'quantity'   => 1,
        'unit_price' => 100.00,
        'price'      => 100.00,
        'total'      => 100.00,
    ]);

    $marker = "order_number:{$order->number};order_id:{$order->getKey()}";

    Http::fake([
        'https://saskaita.vercel.app/api/initiate' => Http::response('%PDF-1.4 command-binary', 200, [
            'Content-Type' => 'application/pdf',
        ]),
        'https://saskaita.vercel.app/api/actions/list-invoices' => Http::response([
            'invoices' => [
                [
                    'id'           => 'ext-cmd-1',
                    'series'       => 'SER',
                    'number'       => 42,
                    'full_number'  => 'SER-0042',
                    'type'         => 'sf',
                    'notes'        => $marker,
                    'total_amount' => 121.00,
                    'payer_email'  => (string) $order->user?->email,
                ],
            ],
        ], 200),
    ]);

    $this->artisan("orders:invoices:backfill --order-id={$order->getKey()} --allow-unpaid --force")
        ->assertExitCode(0);

    $this->assertDatabaseHas('order_invoices', [
        'order_id'        => $order->getKey(),
        'status'          => OrderInvoice::STATUS_READY,
        'generation_mode' => OrderInvoice::MODE_MANUAL,
    ]);

    Http::assertSent(static function (Request $request): bool {
        return str_ends_with($request->url(), '/api/initiate');
    });
});
