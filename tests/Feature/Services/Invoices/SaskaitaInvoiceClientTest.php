<?php

declare(strict_types=1);

use App\Services\Invoices\SaskaitaInvoiceClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\Support\PdfFixture;

beforeEach(function (): void {
    Config::set('invoices.base_url', 'https://saskaita.vercel.app');
    Config::set('invoices.auth_bearer', '');
    Config::set('invoices.timeout_seconds', 5);
    Config::set('invoices.retry_times', 1);
    Config::set('invoices.retry_sleep_ms', 50);
});

it('strips null fields from initiate payload before sending request', function (): void {
    $capturedPayload = null;

    Http::fake(function (Request $request) use (&$capturedPayload) {
        $capturedPayload = $request->data();

        return Http::response(PdfFixture::binary('Saskaita initiate response'), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    });

    $payload = [
        'api_token'      => 'token',
        'invoice_type'   => 'sf',
        'notes'          => '',
        'total_chipping' => 0,
        'total_discount' => 0,
        'total_amount'   => 121,
        'products'       => [
            [
                'description' => 'Service',
                'quantity'    => 1,
                'price'       => 100,
                'meta'        => null,
            ],
        ],
        'billing' => [
            'name'         => 'Buyer',
            'isJuridical'  => false,
            'company_code' => null,
            'vat_code'     => null,
        ],
        'delivery' => [
            'name'    => 'Buyer',
            'address' => null,
        ],
        'payer' => [
            'name'    => 'Buyer',
            'email'   => 'buyer@example.test',
            'website' => null,
        ],
    ];

    $binary = app(SaskaitaInvoiceClient::class)->initiateInvoice($payload);

    expect($binary)->toStartWith('%PDF-')
        ->and($binary)->toContain('Lorem ipsum')
        ->and($capturedPayload)->toBeArray()
        ->and(array_key_exists('company_code', $capturedPayload['billing']))->toBeFalse()
        ->and(array_key_exists('vat_code', $capturedPayload['billing']))->toBeFalse()
        ->and(array_key_exists('address', $capturedPayload['delivery']))->toBeFalse()
        ->and(array_key_exists('website', $capturedPayload['payer']))->toBeFalse()
        ->and(array_key_exists('meta', $capturedPayload['products'][0]))->toBeFalse()
        ->and($capturedPayload['seller']['website'] ?? null)->toBe('https://example.com');
});

it('retries with fallback seller website when provider returns seller website validation error', function (): void {
    $capturedPayloads = [];

    Http::fake(function (Request $request) use (&$capturedPayloads) {
        $capturedPayloads[] = $request->data();

        if (count($capturedPayloads) === 1) {
            return Http::response([
                'statusCode'    => 400,
                'statusMessage' => 'validation-error',
                'message'       => '[pdf-validation]: H3Error: [{"code":"invalid_format","format":"url","path":["seller","website"],"message":"Invalid URL"}]',
            ], 400);
        }

        return Http::response(PdfFixture::binary('Saskaita retry response'), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    });

    $payload = [
        'api_token'      => 'token',
        'invoice_type'   => 'sf',
        'notes'          => '',
        'total_chipping' => 0,
        'total_discount' => 0,
        'total_amount'   => 121,
        'products'       => [
            [
                'description' => 'Service',
                'quantity'    => 1,
                'price'       => 100,
            ],
        ],
        'billing' => [
            'name'        => 'Buyer',
            'isJuridical' => false,
        ],
        'delivery' => [
            'name' => 'Buyer',
        ],
        'payer' => [
            'name'  => 'Buyer',
            'email' => 'buyer@example.test',
        ],
        'seller' => [
            'website' => 'https://egistatyba.test',
        ],
    ];

    $binary = app(SaskaitaInvoiceClient::class)->initiateInvoice($payload);

    expect($binary)->toStartWith('%PDF-')
        ->and($binary)->toContain('Lorem ipsum')
        ->and($capturedPayloads)->toHaveCount(2)
        ->and($capturedPayloads[0]['seller']['website'] ?? null)->toBe('https://example.com')
        ->and($capturedPayloads[1]['seller']['website'] ?? null)->toBe('https://www.example.com');
});
