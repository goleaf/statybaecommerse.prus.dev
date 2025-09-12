<?php

declare(strict_types=1);

namespace App\Services\Debug;

use Illuminate\Support\Facades\Log;

class DiscountDebugCollector
{
    public function logDiscountApplication(string $code, array $context, bool $applied, float $amount): void
    {
        $message = 'Discount application';
        $data = [
            'code' => $code,
            'applied' => $applied,
            'amount' => $amount,
            'context' => $context,
        ];

        if (function_exists('debugbar') && app()->bound('debugbar')) {
            try {
                app('debugbar')->addMessage($data, 'discount');
            } catch (\Throwable $e) {
                // ignore
            }
        }

        Log::debug($message, $data);
    }
}
