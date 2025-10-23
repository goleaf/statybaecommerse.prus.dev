# Security Baseline

This runbook summarizes the default HTTP hardening and rate limiting controls that ship with the application.

## Response Headers

All first-party routes run through the `App\Http\Middleware\AddSecurityHeaders` middleware. The middleware reads its configuration from `config/security.php` and applies it to every successful response.

- Static headers are defined in `security.headers.values`. They include the default clickjacking, MIME sniffing, referrer, and permissions policies used across the storefront and APIs.
- Content Security Policy directives are configured under `security.headers.content_security_policy`. The middleware compiles the directives into the final `Content-Security-Policy` header automatically.
- You can toggle the middleware globally by setting `security.headers.enabled` (or the `SECURITY_HEADERS_ENABLED` environment variable).

## Rate Limiting

Baseline rate limits also live in `config/security.php` and are registered by `App\Providers\SecurityServiceProvider` during boot.

### API limits

| Limiter | Default | Keying strategy |
| --- | --- | --- |
| `api.default` | 60 requests/minute | User ID for authenticated calls, otherwise IP address |
| `api.notifications` | 60 requests/minute | Same as `api.default`, plus `|notifications` suffix |
| `api.autocomplete` | 30 requests/minute | Same as `api.default`, plus `|autocomplete` suffix |

Override the API defaults with the `API_RATE_LIMIT_*` environment variables.

### Authentication limits

Authentication flows use configurable limits that default to conservative values:

| Flow | Attempts | Decay window | Notes |
| --- | --- | --- | --- |
| Login | 5 attempts | 60 seconds | Tracked per lowercase email + request IP; cleared after a successful login. |
| Password reset | 5 attempts | 5 minutes | Tracked per lowercase email + request IP; counts every reset email request. |

Set `AUTH_RATE_LIMIT_LOGIN_*` and `AUTH_RATE_LIMIT_PASSWORD_RESET_*` environment variables to tune these thresholds.

## Operational Tips

- The rate limiter keys always fall back to `unknown` when an IP address cannot be determined, so clearing `RateLimiter` state with either the computed key or the fallback will reset the counters.
- Feature tests in `tests/Feature/SecurityHeadersTest.php` and `tests/Feature/Auth/LoginRateLimitingTest.php` cover the middleware and authentication throttles. Run them whenever the security configuration changes.
