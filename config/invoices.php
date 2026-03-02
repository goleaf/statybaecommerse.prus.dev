<?php

declare(strict_types=1);

return [
    'enabled'         => (bool) env('INVOICES_ENABLED', false),
    'base_url'        => (string) env('INVOICES_BASE_URL', 'https://saskaita.vercel.app'),
    'api_token'       => (string) env('INVOICES_API_TOKEN', ''),
    'auth_bearer'     => (string) env('INVOICES_API_AUTH_BEARER', ''),
    'timeout_seconds' => (int) env('INVOICES_TIMEOUT_SECONDS', 20),
    'retry_times'     => (int) env('INVOICES_RETRY_TIMES', 3),
    'retry_sleep_ms'  => (int) env('INVOICES_RETRY_SLEEP_MS', 250),
    'seller_website'  => (string) env('INVOICES_SELLER_WEBSITE', ''),

    // Provider-supported invoice defaults.
    'default_invoice_type' => (string) env('INVOICES_DEFAULT_TYPE', 'sf'),
    'default_tax_rate'     => (int) env('INVOICES_DEFAULT_TAX_RATE', 21),
    'default_tax_type'     => (string) env('INVOICES_DEFAULT_TAX_TYPE', 'excluded'),
    'default_language'     => (string) env('INVOICES_DEFAULT_LANGUAGE', 'lt'),
];
