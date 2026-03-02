<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\User;
use App\Services\Payments\MontonioService;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Config::set('montonio.access_key', 'test_access_key');
    Config::set('montonio.secret_key', 'test_secret_key_that_is_long_enough_for_hs256');
    Config::set('montonio.sandbox', true);
    Config::set('montonio.sandbox_url', 'https://sandbox-stargate.montonio.com/api');

    $this->service = app(MontonioService::class);

    $this->user = User::factory()->create();
    $this->order = Order::factory()->create([
        'user_id'         => $this->user->id,
        'number'          => 'LT-TEST123',
        'total'           => 100.50,
        'currency'        => 'EUR',
        'billing_address' => [
            'first_name'   => 'John',
            'last_name'    => 'Doe',
            'email'        => 'john@example.com',
            'address'      => 'Test Street 1',
            'city'         => 'Vilnius',
            'country_code' => 'LT',
            'postal_code'  => '01111',
            'region'       => 'Vilniaus',
        ],
    ]);
});

it('can generate a valid JWT payload for creating an order', function () {
    $token = $this->service->createOrderToken($this->order);

    expect($token)->toBeString();

    // Decode and verify contents
    $decoded = (array) JWT::decode($token, new \Firebase\JWT\Key('test_secret_key_that_is_long_enough_for_hs256', 'HS256'));

    expect($decoded)
        ->toHaveKey('accessKey', 'test_access_key')
        ->toHaveKey('merchantReference', 'LT-TEST123')
        ->toHaveKey('currency', 'EUR')
        ->toHaveKey('grandTotal', 100.50)
        ->toHaveKey('returnUrl', route('frontend.checkout.return.montonio'))
        ->toHaveKey('notificationUrl', route('webhooks.payments.montonio'));
});

it('can get a payment URL from Montonio API', function () {
    // Mock the HTTP response
    Http::fake([
        'https://sandbox-stargate.montonio.com/api/orders' => Http::response([
            'paymentUrl' => 'https://sandbox-stargate.montonio.com/payment/xyz-123',
        ], 200),
    ]);

    $paymentUrl = $this->service->getPaymentUrl($this->order);

    expect($paymentUrl)->toBe('https://sandbox-stargate.montonio.com/payment/xyz-123');
});

it('throws an exception if API fails to create order', function () {
    Http::fake([
        'https://sandbox-stargate.montonio.com/api/orders' => Http::response([
            'error' => 'Invalid parameters',
        ], 400),
    ]);

    $this->service->getPaymentUrl($this->order);
})->throws(Exception::class, 'Failed to create Montonio payment order.');

it('can validate and decode a received JWT token', function () {
    $testPayload = [
        'merchantReference' => 'LT-TEST123',
        'paymentStatus'     => 'PAID',
    ];

    $token = JWT::encode($testPayload, 'test_secret_key_that_is_long_enough_for_hs256', 'HS256');
    $decoded = $this->service->validateToken($token);

    expect($decoded)
        ->toHaveKey('merchantReference', 'LT-TEST123')
        ->toHaveKey('paymentStatus', 'PAID');
});

it('throws an exception on invalid token validation', function () {
    $testPayload = [
        'merchantReference' => 'LT-TEST123',
    ];

    // Sign with WRONG key
    $token = JWT::encode($testPayload, 'wrong_secret_key_that_is_long_enough_for_hs256', 'HS256');
    $this->service->validateToken($token);
})->throws(Exception::class, 'Invalid Montonio order token.');
