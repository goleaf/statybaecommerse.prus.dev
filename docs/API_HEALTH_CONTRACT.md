# API Health & Readiness Contract

The monitoring endpoints exposed under `/api/v1` provide lightweight system status snapshots without requiring authentication. Both responses share the same JSON structure and explicitly disable caching so external monitors always retrieve fresh information.

## Endpoints

| Method | Path | Description |
| --- | --- | --- |
| `GET` | `/api/v1/health` | Liveness probe. Verifies that the application can talk to its primary database and cache stores. |
| `GET` | `/api/v1/ready` | Readiness probe. Runs the liveness checks and, when an asynchronous queue driver is configured, validates the queue connection as well. |

All endpoints return the `Cache-Control: no-store, max-age=0` and `Pragma: no-cache` headers.

## Response schema

```jsonc
{
  "status": "ok",            // "ok" when every executed check succeeds, otherwise "error"
  "timestamp": "2024-05-16T09:03:05+00:00",
  "checks": {
    "database": {
      "status": "ok",         // "ok" or "failed"
      "latency_ms": 1.42,      // Rounded execution time of the check
      "message": "..."        // Present only on failure
    },
    "cache": {
      "status": "ok",
      "latency_ms": 0.94,
      "message": "..."
    },
    "queue": {
      "status": "ok",
      "latency_ms": 1.08,
      "message": "...",
      "meta": {
        "connection": "database",
        "driver": "database"
      }
    }
  }
}
```

- The `queue` entry only appears on `/api/v1/ready` when the queue driver is neither `sync` nor `null`.
- A failing check adds a `message` describing the exception that occurred and causes the HTTP status to switch to `503 Service Unavailable`.

## Examples

### Successful liveness response

```http
GET /api/v1/health HTTP/1.1
Accept: application/json

HTTP/1.1 200 OK
Cache-Control: no-store, max-age=0
Pragma: no-cache
Content-Type: application/json

{
  "status": "ok",
  "timestamp": "2024-05-16T09:03:05+00:00",
  "checks": {
    "database": {"status": "ok", "latency_ms": 1.42},
    "cache": {"status": "ok", "latency_ms": 0.94}
  }
}
```

### Readiness failure response

```http
GET /api/v1/ready HTTP/1.1
Accept: application/json

HTTP/1.1 503 Service Unavailable
Cache-Control: no-store, max-age=0
Pragma: no-cache
Content-Type: application/json

{
  "status": "error",
  "timestamp": "2024-05-16T09:05:12+00:00",
  "checks": {
    "database": {
      "status": "failed",
      "latency_ms": 0.37,
      "message": "Simulated failure"
    }
  }
}
```

Monitoring platforms should alert on non-`200` responses or when any individual check reports a `status` of `failed`.
