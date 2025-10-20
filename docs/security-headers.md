# HTTP Security Headers

The application ships with a dedicated `AddSecurityHeaders` middleware that standardises defensive HTTP headers and a sensible Content Security Policy (CSP). The middleware is part of the global stack and is configured through `config/security.php`.

## Default policy

The defaults are tuned for our Laravel + Vite frontend and Livewire components:

- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()`
- `Content-Security-Policy` directives that allow our compiled Vite assets, Livewire, Alpine.js (from the CDN), Swagger UI (in the docs area), and Bunny Fonts.

## Extending the CSP for third-party services

Add any additional hosts or directives in `config/security.php` under `security.headers.content_security_policy`. Each directive accepts an array of sources, so extending the policy for a new CDN is a simple configuration change:

```php
// config/security.php
return [
    'headers' => [
        // ...
        'content_security_policy' => [
            // Existing directives
            'script-src' => [
                "'self'",
                '@nonce',
                'https://unpkg.com',
                'https://cdn.example.com', // New script host
            ],
            'script-src-attr' => [
                "'unsafe-inline'",
            ],
            'img-src' => [
                "'self'",
                'data:',
                'blob:',
                'https://images.example-cdn.com', // External image CDN
            ],
            // Add or override directives as needed
        ],
    ],
];
```

After updating the config, clear the cached configuration if necessary:

```bash
php artisan config:clear
php artisan config:cache
```

For temporary testing you can disable the middleware entirely via `SECURITY_HEADERS_ENABLED=false` in `.env`, but re-enable it before releasing.

## Verifying the policy

Run the dedicated test to confirm the middleware continues to emit the expected headers:

```bash
vendor/bin/pest tests/security/SecurityHeadersTest.php
```

For browser validation, build the production assets (`npm run build`) and browse the storefront with your browser’s developer tools open; the console should stay free of CSP violations.
