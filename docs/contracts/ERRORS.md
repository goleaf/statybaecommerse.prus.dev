# API Error Contract

All API endpoints return errors using a common RFC 7807 inspired envelope. Every response sets `Content-Type: application/problem+json`.

```json
{
  "error": {
    "code": "string",
    "message": "string",
    "details": { "optional": "object" },
    "correlation_id": "uuid"
  }
}
```

- `code` &mdash; Stable, machine-readable identifier for the error condition.
- `message` &mdash; Localized, human readable description suitable for end-users.
- `details` &mdash; Optional structured payload (for example validation errors or retry hints). Omitted as `null` when not needed.
- `correlation_id` &mdash; UUID attached to application logs that helps support teams trace requests. Present on every error response, regardless of environment.

## Standard error codes

| HTTP Status | Code                | Description                                                                 |
| ----------- | ------------------- | --------------------------------------------------------------------------- |
| 400         | `domain_error`      | Domain or business rule violation (for example unsupported model lookup).  |
| 401         | `unauthenticated`   | Authentication failed or credentials were missing.                          |
| 403         | `forbidden`         | Authenticated user lacks permission to perform the action.                  |
| 404         | `resource_not_found`| Target model or route could not be resolved.                                |
| 422         | `validation_error`  | Request payload did not pass validation. `details.errors` lists violations. |
| 429         | `rate_limited`      | Rate limit exceeded. `details.retry_after` may contain retry hints.         |
| 5xx         | `server_error`      | Unexpected exception. Message is generic; diagnostics only in logs.         |
| *varies*    | `http_error`        | Other HTTP exceptions (405, 409, etc.).                                     |

## Examples

### Validation failure (422)

```json
{
  "error": {
    "code": "validation_error",
    "message": "The given data was invalid.",
    "details": {
      "errors": {
        "email": ["The email field is required."]
      }
    },
    "correlation_id": "87b42a5c-7803-45d4-8f89-c5bfd0a9e482"
  }
}
```

### Authentication failure (401)

```json
{
  "error": {
    "code": "unauthenticated",
    "message": "Unauthenticated.",
    "details": null,
    "correlation_id": "3bdb8b8c-b2ab-4ff0-9aef-d4c08a687e4c"
  }
}
```

### Unexpected server error (500)

```json
{
  "error": {
    "code": "server_error",
    "message": "An unexpected error occurred.",
    "details": null,
    "correlation_id": "4efef77a-9abc-41a5-8eac-8b17f6cd1b3f"
  }
}
```

> **Note:** Stack traces never leak in production responses. Use the `correlation_id` to locate log entries for diagnostics.
