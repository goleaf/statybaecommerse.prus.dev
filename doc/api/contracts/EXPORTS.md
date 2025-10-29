# Export Column Contracts

The export subsystem produces CSV, XLSX, and PDF artifacts for the Orders, Products, and Users datasets. Every export shares the same column order, labels, and data types regardless of format to keep downstream integrations stable. This document acts as the canonical contract for those columns and includes example payloads for the Filament bulk action and API download flow.

## Common guarantees

- Files are written to the configured export filesystem disk (defaulting to the secure-media store)
  at `exports/{export_uuid}.{extension}` with filenames mirrored in the database for downstream
  presentation.
- When `config('export.disk')` is not set the service automatically falls back to the current
  `filesystems.default` value, preferring the secure-media disk whenever it is the active default so
  signed download routes remain valid in every environment.
- Signed download URLs are created with the `api.exports.download` route and expire after `config('exports.ttl_minutes')` minutes (24 hours by default).
- All date and time values are rendered in the timezone requested when the export was queued.
- Currency fields are emitted with two decimal places using the `.` decimal separator.
- Numeric totals in PDFs appear in the footer row of the table.

## Orders export

| Key          | Column label   | Type      | Notes                                      |
|--------------|----------------|-----------|--------------------------------------------|
| `number`     | Order Number   | string    | The order number shown in the admin UI.    |
| `status`     | Status         | string    | Internal status slug (pending, shipped…).  |
| `payment_status` | Payment Status | string | Payment state slug.                        |
| `total`      | Grand Total    | currency  | Order total in euros.                      |
| `customer`   | Customer       | string    | Associated user name, blank if missing.    |
| `items`      | Items          | integer   | Count of related `order_items`.            |
| `created_at` | Created At     | datetime  | ISO datetime adjusted to the requested TZ. |

### Example action payload

```json
{
  "entity": "orders",
  "format": "csv",
  "columns": ["number", "status", "payment_status", "total", "customer", "items", "created_at"],
  "filters": {"status": "delivered", "created_from": "2024-01-01"},
  "locale": "en",
  "timezone": "Europe/Vilnius",
  "ids": [12, 45, 46]
}
```

## Products export

| Key        | Column label | Type     | Notes                                        |
|------------|--------------|----------|----------------------------------------------|
| `sku`      | SKU          | string   | Product SKU.                                 |
| `name`     | Name         | string   | Default locale name.                         |
| `status`   | Status       | string   | Product status slug.                         |
| `price`    | Price        | currency | Base price in euros.                         |
| `stock`    | Stock        | integer  | Current inventory quantity.                  |
| `created_at` | Created At | datetime | ISO datetime adjusted to the requested TZ.   |

### Example action payload

```json
{
  "entity": "products",
  "format": "xlsx",
  "columns": ["sku", "name", "status", "price", "stock", "created_at"],
  "filters": {"status": "published"},
  "locale": "lt",
  "timezone": "Europe/Vilnius"
}
```

## Users export

| Key            | Column label | Type     | Notes                                                       |
|----------------|--------------|----------|-------------------------------------------------------------|
| `name`         | Name         | string   | Full name.                                                  |
| `email`        | Email        | string   | Primary email address.                                      |
| `role`         | Role         | string   | Comma separated list of assigned roles.                     |
| `created_at`   | Created At   | datetime | ISO datetime adjusted to the requested timezone.            |
| `last_login_at`| Last Login   | datetime | Nullable; blank when the user never logged in.              |

### Example action payload

```json
{
  "entity": "users",
  "format": "pdf",
  "columns": ["name", "email", "role", "created_at", "last_login_at"],
  "filters": {"preferred_locale": "en", "created_from": "2023-01-01"},
  "locale": "en",
  "timezone": "UTC"
}
```

## Downloading exports

Exports are downloaded via a signed GET request:

```http
GET /api/v1/exports/{id}?expires={timestamp}&signature={signature}
Authorization: Bearer <sanctum_token>
```

The response returns `200 OK` with `Content-Type` matching the requested format and includes a `Content-Disposition` header for attachment downloads. Links return `410 Gone` when expired and `403 Forbidden` when the signature is invalid.
