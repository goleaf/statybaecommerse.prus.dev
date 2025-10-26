# Notification API Contracts

The following JSON Schema contracts describe the request and response payloads produced by the notification endpoints exposed under `/api/v1/notifications`. All schemas conform to the `https://json-schema.org/draft/2020-12/schema` specification.

## Shared Definitions

### `NotificationPayload`

```json
{
  "$id": "https://prus.dev/schemas/notification-payload.json",
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "required": ["id", "notification_class", "type", "urgent", "tags", "is_read", "created_at", "context"],
  "properties": {
    "id": {"type": "string"},
    "notification_class": {"type": "string", "description": "Fully-qualified notification class name."},
    "type": {"type": "string", "description": "Domain category stored in the notification payload."},
    "title": {"type": ["string", "null"]},
    "message": {"type": ["string", "null"]},
    "urgent": {"type": "boolean"},
    "color": {"type": ["string", "null"]},
    "tags": {
      "type": "array",
      "items": {"type": "string"},
      "description": "Distinct semantic tags attached to the notification."
    },
    "is_read": {"type": "boolean"},
    "read_at": {"type": ["string", "null"], "format": "date-time"},
    "created_at": {"type": ["string", "null"], "format": "date-time"},
    "context": {
      "type": "object",
      "description": "Additional key/value metadata stored alongside the notification payload.",
      "additionalProperties": true
    }
  },
  "additionalProperties": false
}
```

### `PaginationMeta`

```json
{
  "$id": "https://prus.dev/schemas/notification-pagination.json",
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "required": ["current_page", "last_page", "per_page", "total", "from", "to"],
  "properties": {
    "current_page": {"type": "integer", "minimum": 1},
    "last_page": {"type": "integer", "minimum": 1},
    "per_page": {"type": "integer", "minimum": 1, "maximum": 100},
    "total": {"type": "integer", "minimum": 0},
    "from": {"type": ["integer", "null"], "minimum": 0},
    "to": {"type": ["integer", "null"], "minimum": 0}
  },
  "additionalProperties": false
}
```

## `GET /api/v1/notifications`

### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `per_page` | integer (1-100) | Optional pagination size (default `25`). |
| `type` | string | Optional notification category filter. |
| `read` | boolean | Optional read-state filter. |

### Response Schema

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "required": ["success", "data", "pagination"],
  "properties": {
    "success": {"const": true},
    "data": {
      "type": "array",
      "items": {"$ref": "https://prus.dev/schemas/notification-payload.json"}
    },
    "pagination": {"$ref": "https://prus.dev/schemas/notification-pagination.json"}
  },
  "additionalProperties": false
}
```

## `GET /api/v1/notifications/search`

### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `q` | string | **Required.** Free-text search term. |
| `per_page` | integer (1-100) | Optional pagination size (default `25`). |
| `type` | string | Optional notification category filter. |
| `read` | boolean | Optional read-state filter. |

### Response Schema

The response payload mirrors the index contract described above.

## `GET /api/v1/notifications/stats`

### Response Schema

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "required": ["success", "data"],
  "properties": {
    "success": {"const": true},
    "data": {
      "type": "object",
      "required": ["total", "read", "unread", "urgent"],
      "properties": {
        "total": {"type": "integer", "minimum": 0},
        "read": {"type": "integer", "minimum": 0},
        "unread": {"type": "integer", "minimum": 0},
        "urgent": {"type": "integer", "minimum": 0}
      },
      "additionalProperties": false
    }
  },
  "additionalProperties": false
}
```

## Mutation Endpoints

The following endpoints return a shared envelope that communicates success and optional payload data.

- `POST /api/v1/notifications/{notification}/mark-read`
- `POST /api/v1/notifications/{notification}/mark-unread`
- `POST /api/v1/notifications/mark-all-read`
- `POST /api/v1/notifications/mark-all-unread`
- `DELETE /api/v1/notifications/{notification}`

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "required": ["success", "message"],
  "properties": {
    "success": {"const": true},
    "message": {"type": "string"},
    "count": {"type": "integer", "minimum": 0},
    "data": {"$ref": "https://prus.dev/schemas/notification-payload.json"}
  },
  "additionalProperties": false
}
```

## Error Envelope

Any authorization, validation, or ownership failures return a JSON body with the following structure:

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "required": ["success", "message"],
  "properties": {
    "success": {"const": false},
    "message": {"type": "string"},
    "errors": {
      "type": "object",
      "additionalProperties": {
        "type": "array",
        "items": {"type": "string"}
      }
    }
  },
  "additionalProperties": false
}
```

These contracts should be referenced by any consumers integrating with the notifications API to guarantee consistent request validation and response handling.
