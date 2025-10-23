# API Error Responses

All JSON API errors now follow the [RFC 7807](https://www.rfc-editor.org/rfc/rfc7807) "problem details" format with additional
metadata so clients can correlate failures and act on structured context. Responses include the header declared by
`config('app.correlation_header')` (defaults to `X-Correlation-ID`) and are encoded with the
`application/problem+json` content type.【F:app/Support/ApiErrorResponse.php†L19-L77】【F:bootstrap/app.php†L66-L332】

## Envelope

Every problem payload exposes the following members:

| Field | Type | Description |
| --- | --- | --- |
| `type` | string | Absolute URI identifying the machine-readable error code. Built from `https://prus.dev/problems/{errorCode}` by default. |
| `title` | string | Stable, English summary derived from the shared error catalogue (`App\Support\ErrorCodes`). |
| `status` | integer | HTTP status code attached to the response. |
| `detail` | string | Localised human-readable explanation of the failure. |
| `instance` | string | Full URL for the request that triggered the error. |
| `error.code` | string | Machine-readable error identifier. |
| `error.context` | object | Optional context describing the failure (domain-specific payload, validation violations, headers, etc.). |
| `correlation.trace_id` | string | The request correlation identifier (also emitted in the response header). |
| `correlation.correlation_id` | string | Alias of `trace_id` for compatibility with existing logging. |
| `meta.locale` | string | Locale selected for translations. |
| `meta.timestamp` | string | ISO-8601 timestamp when the response was generated. |

Example payload:

```json
{
  "type": "https://prus.dev/problems/error.validation",
  "title": "Provided data failed validation checks.",
  "status": 422,
  "detail": "The given data was invalid.",
  "instance": "https://api.prus.dev/api/v1/autocomplete-search",
  "error": {
    "code": "error.validation",
    "context": {
      "violations": [
        {
          "field": "model_class",
          "reason": "The model class field is required.",
          "messages": [
            "The model class field is required."
          ]
        }
      ]
    }
  },
  "correlation": {
    "trace_id": "d7c8b9f0-9ad3-4d18-936d-1e4e01bf49d3",
    "correlation_id": "d7c8b9f0-9ad3-4d18-936d-1e4e01bf49d3"
  },
  "meta": {
    "locale": "en",
    "timestamp": "2024-04-22T10:35:17+00:00"
  }
}
```

## Shared Error Codes

The following baseline codes are enforced across APIs. Feature tests cover these values to guarantee they remain stable for
integrators.【F:app/Support/ErrorCodes.php†L15-L84】【F:tests/Feature/Api/ExceptionHandlingTest.php†L18-L123】

| Code | Status | Title | Typical Scenario |
| --- | --- | --- | --- |
| `error.validation` | 422 | Provided data failed validation checks. | Request payload fails Laravel validation; includes `error.context.violations`. |
| `error.unauthorized` | 401 | Request lacks valid authentication credentials. | Missing/expired tokens, failed authentication guards. |
| `error.forbidden` | 403 | Authenticated request lacks permission. | Ability/authorization gate denies access. |
| `error.not_found` | 404 | Resource requested by the client could not be located. | `abort(404)` and other not-found HTTP exceptions. |
| `error.rate_limited` | 429 | Requests exceeded the configured rate limits. | `ThrottleRequestsException` and other HTTP 429 responses from middleware. |
| `error.server` | 500 | Unexpected server exception occurred while handling the request. | Unhandled runtime exceptions bubbled to the renderer. |

Domain-specific exceptions (for example `orders.not_found` or `inventory.insufficient`) continue to populate
`error.context` with relevant fields; the `detail` string remains localised based on the `Accept-Language` header or the
fallback locale.【F:tests/Feature/DomainExceptionResponseTest.php†L29-L104】

## Correlation & Logging

When rendering API problems the exception handler pushes the trace identifier, request path, HTTP method and detected locale
into the logging context before writing structured log entries. This ensures the `trace_id` found in the response correlates to
log entries captured in Horizon, CloudWatch and other sinks.【F:bootstrap/app.php†L88-L332】

Clients **must** propagate the `correlation_id` header value when retrying or escalating incidents. Doing so lets support teams
join requests to server-side traces without additional metadata.
