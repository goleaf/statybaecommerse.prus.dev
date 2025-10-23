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

Layered limiters now protect each surface with a per-user and per-IP budget. The throttle middleware raises a JSON
`429 Too Many Requests` response and emits a structured warning log whenever either layer is exceeded.

| Limiter | Per-user default | Per-IP default | Notes |
| --- | --- | --- | --- |
| `api.read` (alias `api.default`) | 60 requests/minute | 60 requests/minute | Applies to the entire `/api/v1` namespace unless overridden. |
| `api.write` | 60 requests/minute | 60 requests/minute | Use for mutating operations outside the notifications module. |
| `api.notifications.read` | 60 requests/minute | 60 requests/minute | Guard read-only notification calls. |
| `api.notifications.write` | 60 requests/minute | 60 requests/minute | Guard mark-as-read/unread/delete calls. |
| `api.autocomplete` | 30 requests/minute | 30 requests/minute | Detached from the read limiter so search suggestions stay responsive. |
| `api.profile` | 60 requests/minute | 60 requests/minute | Additional layer on top of the read limiter for `/api/v1/user`. |
| `frontend.checkout` | 10 requests/minute | 10 requests/minute | Applies to storefront checkout APIs. |

Tune the thresholds with the new `*_PER_USER` and `*_PER_IP` environment variables described in `config/security.php`.
Setting a value to `0` or `null` disables that layer while keeping the other intact.

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
