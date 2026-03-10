<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Order;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class MontonioService
{
    private const DEMO_ACCESS_KEY = 'sandbox_access_key_demo';

    private const DEMO_SECRET_KEY = 'sandbox_secret_key_demo';

    private string $accessKey;

    private string $secretKey;

    private string $apiUrl;

    private bool $sandbox;

    private bool $demoMode;

    public function __construct()
    {
        $this->accessKey = config('montonio.access_key', '');
        $this->secretKey = config('montonio.secret_key', '');

        $this->sandbox = (bool) config('montonio.sandbox', true);
        $this->demoMode = (bool) config('montonio.demo_mode', false);
        $this->apiUrl = $this->sandbox ? config('montonio.sandbox_url') : config('montonio.production_url');
    }

    /**
     * Create an order token and construct the URL to redirect the user to Montonio.
     */
    public function getPaymentUrl(Order $order, array $paymentSelection = []): string
    {
        if ($this->shouldUseDemoFlow()) {
            return route('frontend.checkout.return.montonio', [
                'order-token' => $this->createDemoPaidToken($order),
            ]);
        }

        $token = $this->createOrderToken($order, $paymentSelection);

        $response = Http::withToken($token)
            ->post("{$this->apiUrl}/orders", [
                'data' => $token,
            ]);

        if (! $response->successful()) {
            Log::error(__('messages.montonio_order_creation_failed_log'), [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new Exception(__('messages.montonio_failed_to_create_payment_order'));
        }

        $data = $response->json();

        if (isset($data['paymentUrl'])) {
            return $data['paymentUrl'];
        }

        throw new Exception(__('messages.montonio_missing_payment_url'));
    }

    /**
     * Build the JWT payload necessary for initializing an order in Montonio.
     */
    public function createOrderToken(Order $order, array $paymentSelection = []): string
    {
        $payload = [
            'accessKey'         => $this->accessKey,
            'merchantReference' => $order->number,
            'returnUrl'         => route('frontend.checkout.return.montonio'),
            'notificationUrl'   => route('webhooks.payments.montonio'),
            'currency'          => strtoupper((string) $order->currency),
            'grandTotal'        => (float) $order->total,
            'locale'            => app()->getLocale(),
            'billingAddress'    => [
                'firstName'    => $order->billing_address['first_name'] ?? '',
                'lastName'     => $order->billing_address['last_name'] ?? '',
                'email'        => $order->billing_address['email'] ?? '',
                'addressLine1' => $order->billing_address['address'] ?? '',
                'locality'     => $order->billing_address['city'] ?? '',
                'region'       => $order->billing_address['region'] ?? '',
                'postalCode'   => $order->billing_address['postal_code'] ?? '',
                'country'      => strtoupper($order->billing_address['country_code'] ?? 'LT'),
            ],
            // Items are recommended but optional. We pass the grandTotal directly.
            'exp' => time() + 3600, // Token valid for 1 hour
        ];

        $paymentPayload = $this->buildPaymentPayload($order, $paymentSelection);
        if ($paymentPayload !== []) {
            $payload['payment'] = $paymentPayload;
        }

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * Fetch and normalize the enabled Montonio checkout methods for the provided country/currency pair.
     *
     * @return array{
     *     methods: array<string, array{type:string,label:string,logo_url:?string,preview_logos:list<string>}>,
     *     banks: list<array{code:string,name:string,logo_url:?string,ui_position:int,supported_currencies:list<string>}>,
     *     preferred_country: string,
     *     store_name: string
     * }
     */
    public function getCheckoutOptions(?string $countryCode = null, ?string $currency = null): array
    {
        $requestedCountry = strtoupper((string) ($countryCode ?? 'LT'));
        $checkoutCurrency = strtoupper((string) ($currency ?? 'EUR'));

        if (! $this->canFetchPaymentMethods()) {
            return [
                'methods'           => [],
                'banks'             => [],
                'preferred_country' => $requestedCountry,
                'store_name'        => '',
            ];
        }

        $payload = $this->fetchStorePaymentMethods();
        $methods = data_get($payload, 'paymentMethods', []);
        $methods = is_array($methods) ? $methods : [];

        $bankSetup = data_get($methods, 'paymentInitiation.setup', []);
        $bankSetup = is_array($bankSetup) ? $bankSetup : [];

        $preferredCountry = $this->resolvePreferredCountry($requestedCountry, $checkoutCurrency, $bankSetup);
        $banks = $this->normalizeBankPaymentMethods(
            data_get($bankSetup, $preferredCountry . '.paymentMethods', []),
            $checkoutCurrency
        );

        $normalizedMethods = [];

        if (is_array($methods['cardPayments'] ?? null)) {
            $normalizedMethods['cardPayments'] = [
                'type'          => 'cardPayments',
                'label'         => (string) __('ui.card_payments'),
                'logo_url'      => $this->normalizeUrl(data_get($methods, 'cardPayments.logoUrl')),
                'preview_logos' => [],
            ];
        }

        if (is_array($methods['paymentInitiation'] ?? null) && $banks !== []) {
            $normalizedMethods['paymentInitiation'] = [
                'type'          => 'paymentInitiation',
                'label'         => (string) __('ui.bank_payments'),
                'logo_url'      => null,
                'preview_logos' => collect($banks)
                    ->pluck('logo_url')
                    ->filter(static fn (?string $logoUrl): bool => is_string($logoUrl) && $logoUrl !== '')
                    ->take(4)
                    ->values()
                    ->all(),
            ];
        }

        return [
            'methods'           => $normalizedMethods,
            'banks'             => $banks,
            'preferred_country' => $preferredCountry,
            'store_name'        => (string) data_get($payload, 'name', ''),
        ];
    }

    private function shouldUseDemoFlow(): bool
    {
        return $this->sandbox && $this->demoMode;
    }

    /**
     * Build a local token that mimics a successful Montonio sandbox return.
     */
    private function createDemoPaidToken(Order $order): string
    {
        $payload = [
            'accessKey'         => $this->accessKey,
            'merchantReference' => $order->number,
            'paymentStatus'     => 'PAID',
            'currency'          => strtoupper((string) $order->currency),
            'grandTotal'        => (float) $order->total,
            'locale'            => app()->getLocale(),
            'exp'               => time() + 3600,
            'isDemo'            => true,
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * Validate and decode the JWT token returned by Montonio webhooks and redirects.
     */
    public function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));

            return (array) $decoded;
        } catch (Exception $e) {
            Log::error(__('messages.montonio_jwt_validation_failed_log'), ['error' => $e->getMessage()]);
            throw new Exception(__('messages.montonio_invalid_order_token'));
        }
    }

    private function canFetchPaymentMethods(): bool
    {
        return $this->accessKey !== ''
            && $this->secretKey !== ''
            && $this->accessKey !== self::DEMO_ACCESS_KEY
            && $this->secretKey !== self::DEMO_SECRET_KEY;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchStorePaymentMethods(): array
    {
        $cacheKey = sprintf(
            'montonio.payment-methods.%s.%s',
            $this->sandbox ? 'sandbox' : 'production',
            md5($this->accessKey)
        );

        /** @var array<string, mixed> $paymentMethods */
        $paymentMethods = Cache::remember($cacheKey, now()->addMinutes(15), function (): array {
            $response = Http::withToken($this->createStoreToken())
                ->acceptJson()
                ->get("{$this->apiUrl}/stores/payment-methods");

            if (! $response->successful()) {
                Log::warning('Montonio payment methods request failed.', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                throw new Exception(__('messages.montonio_failed_to_fetch_payment_methods'));
            }

            $data = $response->json();

            return is_array($data) ? $data : [];
        });

        return $paymentMethods;
    }

    private function createStoreToken(): string
    {
        return JWT::encode([
            'accessKey' => $this->accessKey,
            'exp'       => time() + 600,
        ], $this->secretKey, 'HS256');
    }

    /**
     * @param  array<string, mixed>  $paymentSelection
     * @return array<string, mixed>
     */
    private function buildPaymentPayload(Order $order, array $paymentSelection): array
    {
        $method = trim((string) ($paymentSelection['method'] ?? ''));

        if ($method === '') {
            return [];
        }

        $methodOptions = array_filter([
            'paymentDescription' => (string) __('ui.payment_for_order_number', ['number' => $order->number]),
            'preferredCountry'   => $paymentSelection['preferred_country'] ?? null,
            'preferredProvider'  => $paymentSelection['preferred_provider'] ?? null,
        ], static fn (mixed $value): bool => ! is_string($value) || trim($value) !== '');

        return array_filter([
            'method'        => $method,
            'methodDisplay' => $this->resolvePaymentMethodDisplay($method),
            'amount'        => (float) $order->total,
            'currency'      => strtoupper((string) $order->currency),
            'methodOptions' => $methodOptions !== [] ? $methodOptions : null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function resolvePaymentMethodDisplay(string $method): string
    {
        return match ($method) {
            'cardPayments'     => (string) __('ui.pay_with_card'),
            'paymentInitiation' => (string) __('ui.pay_with_your_bank'),
            default            => (string) __('enums.payment_method.montonio'),
        };
    }

    /**
     * @param  array<string, mixed>  $bankSetup
     */
    private function resolvePreferredCountry(string $requestedCountry, string $currency, array $bankSetup): string
    {
        if (array_key_exists($requestedCountry, $bankSetup)
            && $this->countrySupportsCurrency($bankSetup[$requestedCountry], $currency)) {
            return $requestedCountry;
        }

        if (array_key_exists('LT', $bankSetup) && $this->countrySupportsCurrency($bankSetup['LT'], $currency)) {
            return 'LT';
        }

        foreach ($bankSetup as $country => $setup) {
            if ($this->countrySupportsCurrency($setup, $currency)) {
                return strtoupper((string) $country);
            }
        }

        return $requestedCountry;
    }

    private function countrySupportsCurrency(mixed $setup, string $currency): bool
    {
        $supportedCurrencies = data_get($setup, 'supportedCurrencies', []);

        if (! is_array($supportedCurrencies) || $supportedCurrencies === []) {
            return true;
        }

        return in_array(
            $currency,
            array_map(static fn (mixed $supportedCurrency): string => strtoupper((string) $supportedCurrency), $supportedCurrencies),
            true
        );
    }

    /**
     * @return list<array{code:string,name:string,logo_url:?string,ui_position:int,supported_currencies:list<string>}>
     */
    private function normalizeBankPaymentMethods(mixed $paymentMethods, string $currency): array
    {
        if (! is_array($paymentMethods)) {
            return [];
        }

        /** @var list<array{code:string,name:string,logo_url:?string,ui_position:int,supported_currencies:list<string>}> $banks */
        $banks = collect($paymentMethods)
            ->filter(static fn (mixed $method): bool => is_array($method))
            ->map(function (array $method): array {
                $supportedCurrencies = $method['supportedCurrencies'] ?? [];
                $supportedCurrencies = is_array($supportedCurrencies) ? $supportedCurrencies : [];

                return [
                    'code'                 => trim((string) ($method['code'] ?? '')),
                    'name'                 => trim((string) ($method['name'] ?? '')),
                    'logo_url'             => $this->normalizeUrl($method['logoUrl'] ?? null),
                    'ui_position'          => is_numeric($method['uiPosition'] ?? null) ? (int) $method['uiPosition'] : PHP_INT_MAX,
                    'supported_currencies' => array_values(array_filter(array_map(
                        static fn (mixed $supportedCurrency): string => strtoupper((string) $supportedCurrency),
                        $supportedCurrencies
                    ))),
                ];
            })
            ->filter(function (array $method) use ($currency): bool {
                if ($method['code'] === '' || $method['name'] === '') {
                    return false;
                }

                if ($method['supported_currencies'] === []) {
                    return true;
                }

                return in_array($currency, $method['supported_currencies'], true);
            })
            ->sortBy([
                ['ui_position', 'asc'],
                ['name', 'asc'],
            ])
            ->values()
            ->all();

        return $banks;
    }

    private function normalizeUrl(mixed $value): ?string
    {
        $url = trim((string) ($value ?? ''));

        return $url !== '' ? $url : null;
    }
}
