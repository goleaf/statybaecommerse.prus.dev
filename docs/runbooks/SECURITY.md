# Security Baseline

## HTTP Security Headers

The application now applies security headers to every HTTP response via the `App\Http\Middleware\AddSecurityHeaders` middleware. Defaults are managed in `config/security.php` and include:

- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy` disabling sensitive browser features
- `Content-Security-Policy-Report-Only` restricting core resource origins

Update the config file if routes require custom policies. When adjusting CSP values, keep the policy in report-only mode until verified in production logs, then migrate to an enforcing `Content-Security-Policy` header.

## Rate Limiting

Centralized rate-limit defaults live in `config/security.php` and are registered by `App\Providers\SecurityServiceProvider`:

- **Login form** – 5 attempts per minute keyed by email and client IP. Exceeding the limit returns HTTP 429.
- **Password reset form** – 3 requests per 10 minutes keyed by email and client IP with 429 responses on saturation.
- **API traffic** – 60 requests per minute keyed by bearer token, `X-API-KEY`, authenticated user ID, or IP. Apply the `throttle:api` middleware to new API routes to enforce these limits.

Use the shared config to tune limits for incident response without modifying core code. Tests covering the login and password reset flows assert that throttling emits HTTP 429 once thresholds are exceeded.
