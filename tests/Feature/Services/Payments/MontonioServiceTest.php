<?php

declare(strict_types=1);

use App\Models\Order;
use App\Services\Payments\MontonioService;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Config::set('montonio.access_key', 'test_access_key');
    Config::set('montonio.secret_key', 'test_secret_key_that_is_long_enough_for_hs256');
    Config::set('montonio.sandbox', true);
    Config::set('montonio.sandbox_url', 'https://sandbox-stargate.montonio.com/api');
    Cache::flush();

    $this->service = app(MontonioService::class);

    $this->order = Order::query()->create([
        'number'          => 'LT-TEST123',
        'status'          => 'pending',
        'total'           => 100.50,
        'currency'        => 'EUR',
        'billing_address' => [
            'first_name'   => 'John',
            'last_name'    => 'Doe',
            'email'        => 'info@egisstatyba.lt',
            'address'      => 'Test Street 1',
            'city'         => 'Vilnius',
            'country_code' => 'LT',
            'postal_code'  => '01111',
            'region'       => 'Vilniaus',
        ],
        'shipping_address' => [
            'first_name'   => 'John',
            'last_name'    => 'Doe',
            'email'        => 'info@egisstatyba.lt',
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

it('can include the selected montonio payment provider in the order token', function () {
    $token = $this->service->createOrderToken($this->order, [
        'method'             => 'paymentInitiation',
        'preferred_country'  => 'LT',
        'preferred_provider' => 'HABALT22',
    ]);

    $decoded = JWT::decode($token, new \Firebase\JWT\Key('test_secret_key_that_is_long_enough_for_hs256', 'HS256'));

    expect($decoded->payment->method)->toBe('paymentInitiation');
    expect($decoded->payment->currency)->toBe('EUR');
    expect($decoded->payment->amount)->toBe(100.50);
    expect($decoded->payment->methodOptions->preferredCountry)->toBe('LT');
    expect($decoded->payment->methodOptions->preferredProvider)->toBe('HABALT22');
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

it('can fetch and normalize checkout options from montonio api', function () {
    Http::fake([
        'https://sandbox-stargate.montonio.com/api/stores/payment-methods' => Http::response([
            'name'           => 'Sandbox store',
            'paymentMethods' => [
                'cardPayments' => [
                    'processor' => 'adyen',
                    'logoUrl'   => 'https://public.montonio.com/images/logos/visa-mc-ap-gp.png',
                ],
                'paymentInitiation' => [
                    'processor' => 'montonio',
                    'setup'     => [
                        'LT' => [
                            'supportedCurrencies' => ['EUR'],
                            'paymentMethods'      => [
                                [
                                    'name'                => 'SEB Lietuva',
                                    'logoUrl'             => 'https://public.montonio.com/images/aspsps_logos/seb.png',
                                    'supportedCurrencies' => ['EUR'],
                                    'uiPosition'          => 2,
                                    'code'                => 'CBVILT2X',
                                ],
                                [
                                    'name'                => 'Swedbank Lietuva',
                                    'logoUrl'             => 'https://public.montonio.com/images/aspsps_logos/swedbank.png',
                                    'supportedCurrencies' => ['EUR'],
                                    'uiPosition'          => 1,
                                    'code'                => 'HABALT22',
                                ],
                                [
                                    'name'                => 'Polish only bank',
                                    'logoUrl'             => 'https://public.montonio.com/images/aspsps_logos/example.png',
                                    'supportedCurrencies' => ['PLN'],
                                    'uiPosition'          => 3,
                                    'code'                => 'PLONLY',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $options = $this->service->getCheckoutOptions('LT', 'EUR');

    expect($options['preferred_country'])->toBe('LT');
    expect($options['methods'])->toHaveKeys(['cardPayments', 'paymentInitiation']);
    expect($options['methods']['cardPayments']['logo_url'])->toBe('https://public.montonio.com/images/logos/visa-mc-ap-gp.png');
    expect($options['banks'])->toHaveCount(2);
    expect($options['banks'][0]['code'])->toBe('HABALT22');
    expect($options['banks'][1]['code'])->toBe('CBVILT2X');
    expect($options['methods']['paymentInitiation']['preview_logos'])->toBe([
        'https://public.montonio.com/images/aspsps_logos/swedbank.png',
        'https://public.montonio.com/images/aspsps_logos/seb.png',
    ]);
});

it('trims montonio credentials before signing payment method requests', function () {
    Config::set('montonio.access_key', ' test_access_key ');
    Config::set('montonio.secret_key', ' test_secret_key_that_is_long_enough_for_hs256 ');
    Cache::flush();

    $service = app(MontonioService::class);

    Http::fake([
        'https://sandbox-stargate.montonio.com/api/stores/payment-methods' => Http::response([
            'name'           => 'Sandbox store',
            'paymentMethods' => [
                'cardPayments' => [
                    'processor' => 'adyen',
                    'logoUrl'   => 'https://public.montonio.com/images/logos/visa-mc-ap-gp.png',
                ],
            ],
        ], 200),
    ]);

    $service->getCheckoutOptions('LT', 'EUR');

    Http::assertSent(function (Request $request): bool {
        $authorizationHeader = $request->header('Authorization');
        $token = preg_replace('/^Bearer\s+/i', '', (string) ($authorizationHeader[0] ?? ''));
        $decoded = JWT::decode($token, new \Firebase\JWT\Key('test_secret_key_that_is_long_enough_for_hs256', 'HS256'));

        expect($decoded->accessKey)->toBe('test_access_key');

        return true;
    });
});

it('throws a specific exception when montonio rejects the payment methods token', function () {
    Http::fake([
        'https://sandbox-stargate.montonio.com/api/stores/payment-methods' => Http::response([
            'message'    => 'INVALID_TOKEN',
            'error'      => 'Forbidden',
            'statusCode' => 403,
        ], 403),
    ]);

    try {
        $this->service->getCheckoutOptions('LT', 'EUR');
        $this->fail('Expected Montonio credential exception was not thrown.');
    } catch (Exception $e) {
        expect($e->getMessage())->toBe(__('messages.montonio_invalid_payment_methods_credentials'));
    }
});

it('throws a specific exception when the montonio store cannot be found for payment methods', function () {
    Http::fake([
        'https://sandbox-stargate.montonio.com/api/stores/payment-methods' => Http::response([
            'message'    => 'STORE_NOT_FOUND',
            'error'      => 'Unauthorized',
            'statusCode' => 401,
        ], 401),
    ]);

    try {
        $this->service->getCheckoutOptions('LT', 'EUR');
        $this->fail('Expected Montonio store not found exception was not thrown.');
    } catch (Exception $e) {
        expect($e->getMessage())->toBe(__('messages.montonio_payment_methods_store_not_found'));
    }
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
