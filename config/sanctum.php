<?php

declare(strict_types=1);

use Laravel\Sanctum\Sanctum;

// Build a safe, comma-delimited list of stateful domains.
$defaultStateful = sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort(),
);

$statefulEnv = env('SANCTUM_STATEFUL_DOMAINS', $defaultStateful);
$statefulList = is_string($statefulEnv) ? $statefulEnv : $defaultStateful;

return [
    /*
     * Register the domains that should receive stateful API cookies.
     *
     * @see https://laravel.com/docs/sanctum#stateful-domains
     */
    'stateful' => explode(',', $statefulList),

    /*
     * Guards that Sanctum should inspect before falling back to API tokens.
     */
    'guard' => ['web'],

    /*
     * Number of minutes until issued tokens expire. Null keeps tokens evergreen
     * unless explicitly revoked or a custom expiration is set per token.
     */
    'expiration' => null,

    /*
     * Optional prefix that will be prepended to generated tokens so leaked
     * secrets are easier to discover via security scanning tooling.
     */
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
     * Middleware stack that should wrap first-party SPA authentication flows.
     */
    'middleware' => [
        'authenticate_session' => \Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => \Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
