<?php

declare(strict_types=1);

namespace App\Support\Security;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

final class CspNonce
{
    public const CONTAINER_KEY = 'security.csp_nonce';

    private const REQUEST_ATTRIBUTE = 'security.csp_nonce';

    public static function resolve(Request $request, ?Container $container = null): string
    {
        $container ??= app();

        $fromRequest = $request->attributes->get(self::REQUEST_ATTRIBUTE);
        if (is_string($fromRequest) && $fromRequest !== '') {
            self::storeGlobally($fromRequest, $container);

            return $fromRequest;
        }

        $current = self::current($container);
        if ($current !== null) {
            $request->attributes->set(self::REQUEST_ATTRIBUTE, $current);

            return $current;
        }

        $nonce = self::generate();
        $request->attributes->set(self::REQUEST_ATTRIBUTE, $nonce);
        self::storeGlobally($nonce, $container);

        return $nonce;
    }

    public static function current(?Container $container = null): ?string
    {
        $container ??= app();

        if (! $container->bound(self::CONTAINER_KEY)) {
            return null;
        }

        $resolved = $container->make(self::CONTAINER_KEY);

        return is_string($resolved) && $resolved !== '' ? $resolved : null;
    }

    public static function generate(): string
    {
        return base64_encode(random_bytes(32));
    }

    public static function storeGlobally(string $nonce, ?Container $container = null): void
    {
        $container ??= app();
        $container->instance(self::CONTAINER_KEY, $nonce);

        if ($container->bound('view')) {
            View::share('cspNonce', $nonce);
        }
    }
}
