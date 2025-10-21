# API Error Handling Enhancements

The API now exposes localized, structured problem responses across all domain and framework level exceptions.

## Error code helper

* `App\Support\ErrorCodes` defines the canonical list of machine-friendly error codes.
* `ErrorCodes::title()` and `ErrorCodes::message()` look up locale-aware strings from `lang/*/errors.php`.
* Domain exceptions extend `App\Exceptions\Domain\DomainException` and validate the supplied code via `ErrorCodes::assertValid()`.

## Problem details responses

* `App\Support\ApiErrorResponse::problem()` enriches each RFC&nbsp;7807 payload with correlation identifiers and locale metadata.
* Every exception renderer in `bootstrap/app.php` now fetches localized titles and messages using the helper before falling back to framework defaults.
* HTTP exceptions that bubble up from the framework also inherit the translated messages based on status code.

## Locale detection

* `App\Http\Middleware\SetLocale` honours the `Accept-Language` header, including regional variants (for example `en-GB`).
* The middleware continues to respect explicit locale query parameters, session values, cookies, and authenticated user preferences.
* The resolved locale is propagated to JSON error responses and exposed through the `Content-Language` header.

## Translation coverage

* `lang/en/errors.php`, `lang/lt/errors.php`, `lang/de/errors.php`, and `lang/ru/errors.php` now ship localized titles and detailed messages for each error code.
* These files also retain translator comments to preserve context when updating copy in the future.
