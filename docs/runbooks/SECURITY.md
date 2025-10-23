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

All API limiters enforce both per-user and per-IP budgets. Each request consumes from both buckets when a user session is presen
t; unauthenticated calls only count against the IP budget.

| Limiter | Per-user | Per-IP | Notes |
| --- | --- | --- | --- |
| `api.read` (alias: `api.default`) | 60 requests/minute | 60 requests/minute | Shared by health, readiness, and search endpoints. |
| `api.write` | 60 requests/minute | 60 requests/minute | Baseline limiter for mutating endpoints. |
| `api.notifications.read` | 60 requests/minute | 60 requests/minute | Covers notification list, stats, and search endpoints. |
| `api.notifications.write` | 60 requests/minute | 60 requests/minute | Applies to notification mark-as-read/unread and delete flows. |
| `api.autocomplete` | 30 requests/minute | 30 requests/minute | Dedicated limiter for the autocomplete POST endpoint. |

Override the API defaults with the `API_RATE_LIMIT_*` environment variables. Exceeding a budget logs a warning with the request'
s correlation ID for traceability.

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
