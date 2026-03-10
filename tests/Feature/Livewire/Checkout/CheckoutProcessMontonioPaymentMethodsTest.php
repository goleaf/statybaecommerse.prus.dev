<?php

declare(strict_types=1);

use App\Livewire\Pages\CheckoutProcess;
use App\Models\Product;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    Config::set('montonio.access_key', 'test_access_key');
    Config::set('montonio.secret_key', 'test_secret_key_that_is_long_enough_for_hs256');
    Config::set('montonio.sandbox', true);
    Config::set('montonio.sandbox_url', 'https://sandbox-stargate.montonio.com/api');
});

it('hydrates montonio payment method and bank selections from the api response', function (): void {
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
                                    'name'                => 'Swedbank Lietuva',
                                    'logoUrl'             => 'https://public.montonio.com/images/aspsps_logos/swedbank.png',
                                    'supportedCurrencies' => ['EUR'],
                                    'uiPosition'          => 1,
                                    'code'                => 'HABALT22',
                                ],
                                [
                                    'name'                => 'SEB Lietuva',
                                    'logoUrl'             => 'https://public.montonio.com/images/aspsps_logos/seb.png',
                                    'supportedCurrencies' => ['EUR'],
                                    'uiPosition'          => 2,
                                    'code'                => 'CBVILT2X',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $product = Product::factory()->create([
        'price'  => 120,
        'weight' => 1.0,
    ]);

    session()->put('cart', [
        [
            'product_id' => $product->getKey(),
            'price'      => 120,
            'quantity'   => 1,
            'name'       => $product->name,
            'sku'        => $product->sku,
        ],
    ]);

    $component = Livewire::test(CheckoutProcess::class);

    expect($component->get('montonioPaymentMethodOptions'))->toHaveKeys(['cardPayments', 'paymentInitiation']);
    expect($component->get('selectedMontonioPaymentMethodType'))->toBe('paymentInitiation');
    expect($component->get('selectedMontonioBankCode'))->toBe('HABALT22');

    $component
        ->set('currentStep', 3)
        ->assertSee(__('ui.choose_payment_type'))
        ->assertSee(__('ui.card_payments'))
        ->assertSee(__('ui.bank_payments'))
        ->assertSee(__('ui.choose_bank'));
});

it('shows only the montonio fetch error when payment methods cannot be loaded', function (): void {
    Http::fake([
        'https://sandbox-stargate.montonio.com/api/stores/payment-methods' => Http::response([
            'message'    => 'INVALID_TOKEN',
            'error'      => 'Forbidden',
            'statusCode' => 403,
        ], 403),
    ]);

    $product = Product::factory()->create([
        'price'  => 120,
        'weight' => 1.0,
    ]);

    session()->put('cart', [
        [
            'product_id' => $product->getKey(),
            'price'      => 120,
            'quantity'   => 1,
            'name'       => $product->name,
            'sku'        => $product->sku,
        ],
    ]);

    Livewire::test(CheckoutProcess::class)
        ->set('currentStep', 3)
        ->assertSee(__('messages.montonio_invalid_payment_methods_credentials'))
        ->assertDontSee(__('ui.payment_options_will_appear_here_when_montonio_api_methods_are_available'));
});
