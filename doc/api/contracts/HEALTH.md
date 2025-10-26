# Health & Readiness Endpoints

## Base URL

```
/api/v1
```

## `GET /api/v1/health`

Returns liveness information and dependency check results.

- **Cache-Control**: `no-store`
- **Success (200)**
  ```json
  {
    "status": "ok",
    "timestamp": "2024-01-01T12:00:00+00:00",
    "version": {
      "hash": "unknown"
    },
    "checks": {
      "database": { "status": "ok" },
      "cache": { "status": "ok" },
      "queue": { "status": "ok", "optional": true }
    }
  }
  ```
- **Failure (503)** – if any non-optional check fails.
  ```json
  {
    "status": "error",
    "timestamp": "2024-01-01T12:00:05+00:00",
    "version": {
      "hash": "unknown"
    },
    "checks": {
      "database": {
        "status": "failed",
        "message": "Database connection failed"
      },
      "cache": { "status": "ok" },
      "queue": { "status": "ok", "optional": true }
    }
  }
  ```

## `GET /api/v1/ready`

Provides readiness information. Shares the same payload structure and caching rules as `/health`. Any failing required check (database or cache) returns HTTP 503. Optional checks (queue connectivity) are surfaced in the response but do not affect the HTTP status.

## Version Hash

The `version.hash` field resolves from `config('app.version_hash')`, `config('app.commit_hash')`, or the `APP_VERSION_HASH` environment variable. If none are defined it falls back to `"unknown"`.
