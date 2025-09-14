<?php

declare(strict_types=1);

namespace App\Services\Debug;

use Illuminate\Support\Facades\Log;

/**
 * EcommerceDebugCollector
 * 
 * Service class containing business logic and external integrations.
 */
class EcommerceDebugCollector
{
    public function logCartOperation(string $operation, array $data = []): void
    {
        $payload = ['operation' => $operation, 'data' => $data];
        if (function_exists('debugbar') && app()->bound('debugbar')) {
            try {
                app('debugbar')->addMessage($payload, 'cart');
            } catch (\Throwable $e) {
                // ignore
            }
        }
        Log::debug('Cart operation', $payload);
    }

    public function logOrder(string $operation, string $orderNumber, array $data = []): void
    {
        $payload = compact('operation', 'orderNumber', 'data');
        if (function_exists('debugbar') && app()->bound('debugbar')) {
            try {
                app('debugbar')->addMessage($payload, 'order');
            } catch (\Throwable $e) {
                // ignore
            }
        }
        Log::debug('Order log', $payload);
    }
}
