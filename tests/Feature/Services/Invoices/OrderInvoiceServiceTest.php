<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\Service;
use App\Models\User;
use App\Services\Invoices\OrderInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Config::set('invoices.enabled', false);
    Config::set('invoices.base_url', 'https://saskaita.vercel.app');
    Config::set('invoices.api_token', 'test-api-token');
    Config::set('invoices.auth_bearer', '');
    Config::set('invoices.timeout_seconds', 5);
    Config::set('invoices.retry_times', 1);
    Config::set('invoices.retry_sleep_ms', 50);

    Storage::fake('secure-media');
});

it('generates and stores an invoice PDF for a paid order', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'        => $user->getKey(),
        'number'         => 'ORD-INV-READY-1',
        'payment_status' => 'paid',
        'total'          => 121.00,
    ]);

    $order->items()->create([
        'name'       => 'Invoice test item',
        'sku'        => 'INV-ITEM-1',
        'quantity'   => 1,
        'unit_price' => 100.00,
        'price'      => 100.00,
        'total'      => 100.00,
    ]);

    Config::set('invoices.enabled', true);
    $orderMarker = "order_number:{$order->number};order_id:{$order->getKey()}";

    Http::fake([
        'https://saskaita.vercel.app/api/initiate' => Http::response('%PDF-1.4 fake-binary', 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="SER-0001.pdf"',
        ]),
        'https://saskaita.vercel.app/api/actions/list-invoices' => Http::response([
            'invoices' => [
                [
                    'id'           => 'ext-1001',
                    'series'       => 'SER',
                    'number'       => 1,
                    'full_number'  => 'SER-0001',
                    'type'         => 'sf',
                    'notes'        => $orderMarker,
                    'total_amount' => 121.00,
                    'payer_email'  => (string) $order->user?->email,
                ],
            ],
        ], 200),
    ]);

    $invoice = app(OrderInvoiceService::class)->generateForOrder($order->refresh());

    expect($invoice)->toBeInstanceOf(OrderInvoice::class)
        ->and($invoice?->status)->toBe(OrderInvoice::STATUS_READY)
        ->and($invoice?->external_invoice_id)->toBe('ext-1001')
        ->and($invoice?->file)->not->toBeNull();

    $invoice = $invoice->refresh();

    Storage::disk('secure-media')->assertExists((string) $invoice->file?->path);

    $this->assertDatabaseHas('order_invoices', [
        'order_id'            => $order->getKey(),
        'status'              => OrderInvoice::STATUS_READY,
        'external_invoice_id' => 'ext-1001',
        'full_number'         => 'SER-0001',
        'is_current'          => true,
    ]);

    $this->assertDatabaseHas('files', [
        'id'            => $invoice->file_id,
        'fileable_type' => Order::class,
        'fileable_id'   => $order->getKey(),
        'uploaded_by'   => $user->getKey(),
    ]);

    Http::assertSent(function (Request $request) use ($order): bool {
        if (! str_ends_with($request->url(), '/api/initiate')) {
            return false;
        }

        $payload = $request->data();

        return ($payload['api_token'] ?? null) === 'test-api-token'
            && ($payload['invoice_type'] ?? null) === 'sf'
            && array_key_exists('total_chipping', $payload)
            && array_key_exists('total_discount', $payload)
            && array_key_exists('total_amount', $payload)
            && is_array($payload['billing'] ?? null)
            && is_array($payload['delivery'] ?? null)
            && is_array($payload['payer'] ?? null)
            && is_array($payload['seller'] ?? null)
            && filter_var($payload['seller']['website'] ?? null, FILTER_VALIDATE_URL) !== false
            && is_array($payload['products'] ?? null)
            && str_contains((string) ($payload['notes'] ?? ''), "order_number:{$order->number};order_id:{$order->getKey()}");
    });
});

it('stores regenerated invoice PDFs as unique files and keeps document type metadata', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'        => $user->getKey(),
        'number'         => 'ORD-INV-UNIQUE-FILES-1',
        'payment_status' => 'paid',
        'total'          => 121.00,
    ]);

    $order->items()->create([
        'name'       => 'Invoice unique file item',
        'sku'        => 'INV-UNIQUE-ITEM-1',
        'quantity'   => 1,
        'unit_price' => 100.00,
        'price'      => 100.00,
        'total'      => 100.00,
    ]);

    Config::set('invoices.enabled', true);
    $orderMarker = "order_number:{$order->number};order_id:{$order->getKey()}";
    $attempt = 0;

    Http::fake(function (Request $request) use (&$attempt, $order, $orderMarker) {
        if (str_ends_with($request->url(), '/api/initiate')) {
            $attempt++;

            return Http::response("%PDF-1.4 unique-binary-{$attempt}", 200, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        if (str_ends_with($request->url(), '/api/actions/list-invoices')) {
            return Http::response([
                'invoices' => [
                    [
                        'id'           => 'ext-unique-1',
                        'series'       => 'SER',
                        'number'       => 99,
                        'full_number'  => 'SER-0099',
                        'type'         => 'kpsf',
                        'notes'        => $orderMarker,
                        'total_amount' => 121.00,
                        'payer_email'  => (string) $order->user?->email,
                    ],
                ],
            ], 200);
        }

        return Http::response([], 404);
    });

    app(OrderInvoiceService::class)->generateForOrder($order->refresh(), true, OrderInvoice::MODE_MANUAL, 'kpsf');
    app(OrderInvoiceService::class)->generateForOrder($order->refresh(), true, OrderInvoice::MODE_MANUAL, 'kpsf');

    /** @var \Illuminate\Database\Eloquent\Collection<int, OrderInvoice> $latestInvoices */
    $latestInvoices = OrderInvoice::query()
        ->where('order_id', $order->getKey())
        ->orderByDesc('id')
        ->limit(2)
        ->get();

    expect($latestInvoices)->toHaveCount(2);

    $first = $latestInvoices[1]->load('file');
    $second = $latestInvoices[0]->load('file');

    expect($first->file)->not->toBeNull()
        ->and($second->file)->not->toBeNull()
        ->and($first->file?->path)->not->toBe($second->file?->path)
        ->and($first->invoice_type)->toBe('kpsf')
        ->and($second->invoice_type)->toBe('kpsf')
        ->and($first->file?->metadata['invoice_type'] ?? null)->toBe('kpsf')
        ->and($second->file?->metadata['invoice_type'] ?? null)->toBe('kpsf');

    Storage::disk('secure-media')->assertExists((string) $first->file?->path);
    Storage::disk('secure-media')->assertExists((string) $second->file?->path);
});

it('falls back to provider-safe seller website when local domain is configured', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'        => $user->getKey(),
        'number'         => 'ORD-INV-SELLER-URL-1',
        'payment_status' => 'paid',
        'total'          => 121.00,
    ]);

    $order->items()->create([
        'name'       => 'Seller URL item',
        'sku'        => 'INV-SELLER-URL-ITEM-1',
        'quantity'   => 1,
        'unit_price' => 100.00,
        'price'      => 100.00,
        'total'      => 100.00,
    ]);

    Config::set('invoices.enabled', true);
    Config::set('invoices.seller_website', 'egistatyba.test');
    $orderMarker = "order_number:{$order->number};order_id:{$order->getKey()}";

    Http::fake([
        'https://saskaita.vercel.app/api/initiate' => Http::response('%PDF-1.4 seller-url-binary', 200, [
            'Content-Type' => 'application/pdf',
        ]),
        'https://saskaita.vercel.app/api/actions/list-invoices' => Http::response([
            'invoices' => [
                [
                    'id'           => 'ext-seller-url-1',
                    'series'       => 'SER',
                    'number'       => 31,
                    'full_number'  => 'SER-0031',
                    'type'         => 'sf',
                    'notes'        => $orderMarker,
                    'total_amount' => 121.00,
                    'payer_email'  => (string) $order->user?->email,
                ],
            ],
        ], 200),
    ]);

    app(OrderInvoiceService::class)->generateForOrder($order->refresh(), true);

    Http::assertSent(function (Request $request): bool {
        if (! str_ends_with($request->url(), '/api/initiate')) {
            return false;
        }

        return ($request->data()['seller']['website'] ?? null) === 'https://example.com';
    });
});

it('falls back to invoice listing by amount and payer email when note marker is unavailable', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'        => $user->getKey(),
        'number'         => 'ORD-INV-LIST-1',
        'payment_status' => 'paid',
        'total'          => 121.00,
        'notes'          => 'priority customer',
    ]);

    $order->items()->create([
        'name'       => 'Invoice fallback item',
        'sku'        => 'INV-ITEM-2',
        'quantity'   => 1,
        'unit_price' => 100.00,
        'price'      => 100.00,
        'total'      => 100.00,
    ]);

    Config::set('invoices.enabled', true);
    $billing = is_array($order->billing_address) ? $order->billing_address : [];
    $shipping = is_array($order->shipping_address) ? $order->shipping_address : [];
    $expectedPayerEmail = (string) ($billing['email'] ?? $shipping['email'] ?? $order->user?->email ?? '');

    Http::fake([
        'https://saskaita.vercel.app/api/initiate' => Http::response('%PDF-1.4 fallback-binary', 200, [
            'Content-Type' => 'application/pdf',
        ]),
        'https://saskaita.vercel.app/api/actions/list-invoices' => Http::response([
            'invoices' => [
                [
                    'id'           => 'ext-older',
                    'notes'        => 'something else',
                    'total_amount' => 12.50,
                    'payer_email'  => 'other@example.com',
                ],
                [
                    'id'           => 'ext-2002',
                    'series'       => 'SER',
                    'number'       => 12,
                    'full_number'  => 'SER-0012',
                    'type'         => 'sf',
                    'notes'        => 'priority customer no-marker',
                    'total_amount' => 121.00,
                    'payer_email'  => $expectedPayerEmail,
                ],
            ],
        ], 200),
    ]);

    $invoice = app(OrderInvoiceService::class)->generateForOrder($order->refresh());

    expect($invoice)->toBeInstanceOf(OrderInvoice::class)
        ->and($invoice?->external_invoice_id)->toBe('ext-2002')
        ->and($invoice?->full_number)->toBe('SER-0012')
        ->and($invoice?->status)->toBe(OrderInvoice::STATUS_READY);

    Http::assertSent(function (Request $request): bool {
        return str_ends_with($request->url(), '/api/actions/list-invoices');
    });
});

it('marks current invoice as refunded for refunded payment states', function (): void {
    $order = Order::factory()->create([
        'payment_status' => 'paid',
    ]);

    $invoice = OrderInvoice::factory()->create([
        'order_id'   => $order->getKey(),
        'status'     => OrderInvoice::STATUS_READY,
        'is_current' => true,
        'failed_at'  => null,
    ]);

    app(OrderInvoiceService::class)->markCurrentInvoiceAsRefunded($order);

    expect($invoice->refresh()->status)->toBe(OrderInvoice::STATUS_REFUNDED)
        ->and($invoice->failed_at)->toBeNull();
});

it('supports generating invoice with explicit invoice type', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'        => $user->getKey(),
        'number'         => 'ORD-INV-TYPE-1',
        'payment_status' => 'paid',
        'total'          => 121.00,
    ]);

    $order->items()->create([
        'name'       => 'Invoice typed item',
        'sku'        => 'INV-TYPE-ITEM-1',
        'quantity'   => 1,
        'unit_price' => 100.00,
        'price'      => 100.00,
        'total'      => 100.00,
    ]);

    Config::set('invoices.enabled', true);
    $orderMarker = "order_number:{$order->number};order_id:{$order->getKey()}";

    Http::fake([
        'https://saskaita.vercel.app/api/initiate' => Http::response('%PDF-1.4 typed-binary', 200, [
            'Content-Type' => 'application/pdf',
        ]),
        'https://saskaita.vercel.app/api/actions/list-invoices' => Http::response([
            'invoices' => [
                [
                    'id'           => 'ext-typed-1',
                    'series'       => 'KPSF',
                    'number'       => 7,
                    'full_number'  => 'KPSF-0007',
                    'type'         => 'kpsf',
                    'notes'        => $orderMarker,
                    'total_amount' => 121.00,
                    'payer_email'  => (string) $order->user?->email,
                ],
            ],
        ], 200),
    ]);

    $invoice = app(OrderInvoiceService::class)->generateForOrder($order->refresh(), true, OrderInvoice::MODE_MANUAL, 'kpsf');

    expect($invoice)->toBeInstanceOf(OrderInvoice::class)
        ->and($invoice?->invoice_type)->toBe('kpsf')
        ->and($invoice?->external_invoice_id)->toBe('ext-typed-1');

    Http::assertSent(function (Request $request): bool {
        if (! str_ends_with($request->url(), '/api/initiate')) {
            return false;
        }

        return ($request->data()['invoice_type'] ?? null) === 'kpsf';
    });
});

it('fails before provider call when invoice recipient email is invalid', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'         => $user->getKey(),
        'number'          => 'ORD-INV-INVALID-EMAIL-1',
        'payment_status'  => 'paid',
        'total'           => 121.00,
        'billing_address' => [
            'full_name' => 'Invalid Email Buyer',
            'email'     => 'not-an-email',
        ],
        'shipping_address' => [
            'full_name' => 'Invalid Email Buyer',
            'email'     => 'also-not-an-email',
        ],
    ]);

    $order->items()->create([
        'name'       => 'Invoice invalid email item',
        'sku'        => 'INV-INVALID-EMAIL-ITEM-1',
        'quantity'   => 1,
        'unit_price' => 100.00,
        'price'      => 100.00,
        'total'      => 100.00,
    ]);

    Config::set('invoices.enabled', true);
    Http::fake();
    $expectedMessage = __('messages.invoice_invalid_recipient_email_for_order', [
        'order' => 'ORD-INV-INVALID-EMAIL-1',
        'email' => 'not-an-email',
    ]);

    expect(fn (): ?OrderInvoice => app(OrderInvoiceService::class)->generateForOrder($order->refresh()))
        ->toThrow(\RuntimeException::class, $expectedMessage);

    Http::assertNothingSent();
});

it('allows manual invoice generation for pending orders', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'        => $user->getKey(),
        'number'         => 'ORD-INV-PENDING-MANUAL-1',
        'payment_status' => 'pending',
        'total'          => 121.00,
    ]);

    $order->items()->create([
        'name'       => 'Pending manual item',
        'sku'        => 'INV-PENDING-ITEM-1',
        'quantity'   => 1,
        'unit_price' => 100.00,
        'price'      => 100.00,
        'total'      => 100.00,
    ]);

    Config::set('invoices.enabled', true);
    $orderMarker = "order_number:{$order->number};order_id:{$order->getKey()}";

    Http::fake([
        'https://saskaita.vercel.app/api/initiate' => Http::response('%PDF-1.4 pending-manual-binary', 200, [
            'Content-Type' => 'application/pdf',
        ]),
        'https://saskaita.vercel.app/api/actions/list-invoices' => Http::response([
            'invoices' => [
                [
                    'id'           => 'ext-pending-manual-1',
                    'series'       => 'SER',
                    'number'       => 33,
                    'full_number'  => 'SER-0033',
                    'type'         => 'sf',
                    'notes'        => $orderMarker,
                    'total_amount' => 121.00,
                    'payer_email'  => (string) $order->user?->email,
                ],
            ],
        ], 200),
    ]);

    $invoice = app(OrderInvoiceService::class)->generateForOrder(
        $order->refresh(),
        true,
        OrderInvoice::MODE_MANUAL,
        'sf',
    );

    expect($invoice)->toBeInstanceOf(OrderInvoice::class)
        ->and($invoice?->status)->toBe(OrderInvoice::STATUS_READY);
});

it('keeps automatic invoice generation disabled for pending orders', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'        => $user->getKey(),
        'number'         => 'ORD-INV-PENDING-AUTO-1',
        'payment_status' => 'pending',
        'total'          => 121.00,
    ]);

    $order->items()->create([
        'name'       => 'Pending auto item',
        'sku'        => 'INV-PENDING-AUTO-ITEM-1',
        'quantity'   => 1,
        'unit_price' => 100.00,
        'price'      => 100.00,
        'total'      => 100.00,
    ]);

    Config::set('invoices.enabled', true);
    Http::fake();

    $invoice = app(OrderInvoiceService::class)->generateForOrder($order->refresh());

    expect($invoice)->toBeNull();
    Http::assertNothingSent();
});

it('builds invoice products from attached services when order items are not billable', function (): void {
    $user = User::factory()->create();
    $service = Service::factory()->create([
        'name'      => 'Installation Service',
        'price'     => 50.00,
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'user_id'        => $user->getKey(),
        'number'         => 'ORD-INV-SERVICE-1',
        'payment_status' => 'paid',
        'total'          => 100.00,
    ]);

    $order->items()->create([
        'name'       => 'Zero priced item',
        'sku'        => 'INV-ZERO-ITEM-1',
        'quantity'   => 1,
        'unit_price' => 0.00,
        'price'      => 0.00,
        'total'      => 0.00,
    ]);

    $order->services()->attach($service->getKey(), [
        'price'    => 50.00,
        'quantity' => 2,
    ]);

    Config::set('invoices.enabled', true);
    $orderMarker = "order_number:{$order->number};order_id:{$order->getKey()}";

    Http::fake([
        'https://saskaita.vercel.app/api/initiate' => Http::response('%PDF-1.4 service-binary', 200, [
            'Content-Type' => 'application/pdf',
        ]),
        'https://saskaita.vercel.app/api/actions/list-invoices' => Http::response([
            'invoices' => [
                [
                    'id'           => 'ext-service-1',
                    'series'       => 'SER',
                    'number'       => 55,
                    'full_number'  => 'SER-0055',
                    'type'         => 'sf',
                    'notes'        => $orderMarker,
                    'total_amount' => 100.00,
                    'payer_email'  => (string) $order->user?->email,
                ],
            ],
        ], 200),
    ]);

    app(OrderInvoiceService::class)->generateForOrder($order->refresh(), true);

    Http::assertSent(function (Request $request): bool {
        if (! str_ends_with($request->url(), '/api/initiate')) {
            return false;
        }

        $products = $request->data()['products'] ?? null;
        if (! is_array($products)) {
            return false;
        }

        return count($products) === 1
            && ($products[0]['description'] ?? null) === 'Installation Service'
            && (float) ($products[0]['price'] ?? 0) === 50.0;
    });
});

it('keeps failed generation attempts in invoice history with request payload context', function (): void {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id'        => $user->getKey(),
        'number'         => 'ORD-INV-FAIL-TRACE-1',
        'payment_status' => 'paid',
        'total'          => 121.00,
    ]);

    $order->items()->create([
        'name'       => 'Invoice fail trace item',
        'sku'        => 'INV-FAIL-TRACE-ITEM-1',
        'quantity'   => 1,
        'unit_price' => 100.00,
        'price'      => 100.00,
        'total'      => 100.00,
    ]);

    Config::set('invoices.enabled', true);

    Http::fake([
        'https://saskaita.vercel.app/api/initiate' => Http::response([
            'statusCode'    => 500,
            'statusMessage' => 'pdf-generation-failed',
        ], 500),
    ]);

    expect(fn (): ?OrderInvoice => app(OrderInvoiceService::class)->generateForOrder($order->refresh(), true))
        ->toThrow(RuntimeException::class);

    $invoice = OrderInvoice::query()
        ->where('order_id', $order->getKey())
        ->latest('id')
        ->first();

    expect($invoice)->toBeInstanceOf(OrderInvoice::class)
        ->and($invoice?->status)->toBe(OrderInvoice::STATUS_FAILED)
        ->and($invoice?->provider_payload)->toBeArray()
        ->and($invoice?->provider_payload['initiate_payload']['invoice_type'] ?? null)->toBe('sf')
        ->and($invoice?->provider_payload['failure']['message'] ?? null)->not->toBeNull();
});
