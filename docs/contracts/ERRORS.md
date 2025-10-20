# API Error Contract

All API exceptions are normalized into [RFC 7807](https://datatracker.ietf.org/doc/html/rfc7807) "problem details" envelopes by `App\Support\ApiErrorResponse`. The helper ensures every failure response includes a stable error code, a correlation identifier for observability, and structured details that clients can automate against.

## Envelope shape

```json
{
  "type": "tag:statybaecommerse.prus.dev,2024:error:error.validation",
  "title": "Validation failed",
  "status": 422,
  "detail": "The submitted data was invalid.",
  "instance": "https://example.test/api/example",
  "correlation_id": "d3f1a5ab-8e90-4f2b-bcb4-2d8bfcf16dd2",
  "error": {
    "code": "error.validation",
    "locale": "en",
    "context": {
      "email": ["The email field is required."]
    }
  },
  "meta": {
    "timestamp": "2024-04-16T08:00:00Z"
  }
}
```

### Field reference

| Field | Required | Description |
| --- | --- | --- |
| `type` | ✅ | Stable URI constructed as `tag:statybaecommerse.prus.dev,2024:error:{code}` to aid documentation cross-links. |
| `title` | ✅ | Localised, human-friendly summary of the error. For domain exceptions it maps to the translated message. |
| `status` | ✅ | HTTP status code mirrored in the response status line. |
| `detail` | ✅ | Additional information suitable for end users; equals `title` for domain errors and is context-specific for framework exceptions. |
| `instance` | ✅ | Fully-qualified URL of the request that triggered the problem. |
| `correlation_id` | ✅ | UUID applied to the request/response lifecycle and echoed via the `X-Correlation-ID` header. |
| `error.code` | ✅ | Machine-readable identifier sourced from `App\Support\ErrorCodes`. |
| `error.locale` | ⚠️ | Present when localisation is available (domain exceptions). |
| `error.context` | ⚠️ | Structured payload carrying validation errors or domain-specific placeholders. |
| `meta.timestamp` | ✅ | ISO-8601 timestamp indicating when the response was generated. |

The correlation identifier is also returned in the `X-Correlation-ID` header. When domain translations are involved, the response includes `Content-Language` with the resolved locale so HTTP caches remain coherent.

## Standard codes

The helper maps common framework exceptions onto the shared error codes registered in [`App\Support\ErrorCodes`](ERROR_CODES.md):

| Exception | HTTP | Error code | Notes |
| --- | --- | --- | --- |
| `Illuminate\Validation\ValidationException` | 422 | `error.validation` | `error.context` contains the validator message bag. |
| `Illuminate\Auth\AuthenticationException` | 401 | `error.unauthorized` | Title and detail explain that authentication is required. |
| `Illuminate\Auth\Access\AuthorizationException` | 403 | `error.forbidden` | Signals missing permissions. |
| `Symfony\Component\HttpKernel\Exception\NotFoundHttpException` | 404 | `error.not_found` | Used for missing routes and resources. |
| Any other `Throwable` | 500 | `error.server` | Fallback for uncaught exceptions. |

Domain-level failures (`App\Exceptions\Domain\DomainException`) retain their specific error codes and translation contexts, while still adopting the same RFC 7807 envelope.
