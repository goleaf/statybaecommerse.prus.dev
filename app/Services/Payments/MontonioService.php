<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Order;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class MontonioService
{
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
    public function getPaymentUrl(Order $order): string
    {
        if ($this->shouldUseDemoFlow()) {
            return route('frontend.checkout.return.montonio', [
                'order-token' => $this->createDemoPaidToken($order),
            ]);
        }

        $token = $this->createOrderToken($order);

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
    public function createOrderToken(Order $order): string
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

        return JWT::encode($payload, $this->secretKey, 'HS256');
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
}
