# Notifications API Schema

The notifications API exposes authenticated endpoints for listing, searching, inspecting, and mutating user notifications. All routes require Sanctum tokens and the abilities noted below.

## Common Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | 1-based page index. Defaults to `1`. |
| `per_page` | integer | Page size between `1` and `100`. Defaults to `25`. |
| `sort` | string | Sort field. One of `created_at` (default) or `type`. |
| `direction` | string | Sort direction, either `asc` or `desc`. |
| `type` | string | Optional notification category stored in the notification payload (`data->type`). |
| `read` | boolean | Filter by read (`true`) or unread (`false`) notifications. |

Invalid parameter combinations trigger `422` responses with validation errors.

## GET `/api/v1/notifications`

**Ability:** `notifications.read`

Returns a paginated collection of notifications owned by the authenticated user.

```jsonc
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "notification_type": "App\\Notifications\\OrderNotification",
      "category": "order",
      "title": "Order created",
      "message": "Order #123 created",
      "urgent": false,
      "color": "blue",
      "tags": ["primary"],
      "read_at": "2024-03-01T12:00:00+00:00",
      "created_at": "2024-03-01T11:59:00+00:00",
      "meta": {
        "order_id": 123
      }
    }
  ],
  "meta": {
    "query": {
      "filters": {
        "type": "order",
        "read": null
      },
      "sort": "created_at",
      "direction": "desc",
      "page": 1,
      "per_page": 25
    },
    "pagination": {
      "total": 3,
      "count": 3,
      "per_page": 25,
      "current_page": 1,
      "total_pages": 1
    }
  },
  "links": {
    "first": "https://example.test/api/v1/notifications?page=1",
    "last": "https://example.test/api/v1/notifications?page=1",
    "prev": null,
    "next": null
  }
}
```

## GET `/api/v1/notifications/search`

**Ability:** `notifications.read`

Accepts the common query parameters plus a required `q` term (minimum two characters). Applies the same filtering, pagination, and sorting rules as the index endpoint while matching the term against payload titles, messages, and categories.

## GET `/api/v1/notifications/stats`

**Ability:** `notifications.read`

Returns aggregate counts for the authenticated user.

```json
{
  "success": true,
  "data": {
    "total": 12,
    "read": 9,
    "unread": 3,
    "urgent": 1
  }
}
```

## POST `/api/v1/notifications/{notification}/mark-read`

**Ability:** `notifications.manage`

Marks the notification as read after verifying ownership and returns the normalized payload.

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "notification_type": "App\\Notifications\\OrderNotification",
    "category": "order",
    "title": "Order created",
    "message": "Order #123 created",
    "urgent": false,
    "color": null,
    "tags": [],
    "read_at": "2024-03-01T12:00:00+00:00",
    "created_at": "2024-03-01T11:59:00+00:00",
    "meta": {}
  }
}
```

Ownership violations produce a `404` with the shared problem+json contract and `error.code` set to `not_found`.

## POST `/api/v1/notifications/{notification}/mark-unread`

**Ability:** `notifications.manage`

Marks the notification unread and returns the payload data in the same shape as `mark-read`.

## POST `/api/v1/notifications/mark-all-read`

**Ability:** `notifications.manage`

Marks all unread notifications for the authenticated user as read and returns the number affected.

```json
{
  "success": true,
  "count": 5
}
```

## POST `/api/v1/notifications/mark-all-unread`

**Ability:** `notifications.manage`

Marks all read notifications as unread. Response mirrors the `mark-all-read` payload.

## DELETE `/api/v1/notifications/{notification}`

**Ability:** `notifications.manage`

Deletes the notification after verifying ownership and returns `{ "success": true }` or a `404` error when the record does not belong to the user.
