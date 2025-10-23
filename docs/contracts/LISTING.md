# Listing Contract

All paginated list endpoints follow a common contract so that clients can rely on consistent pagination, sorting, and filtering behaviour.

## Query parameters

| Name | Type | Description |
| ---- | ---- | ----------- |
| `page` | integer (default: `1`) | 1-indexed page number. Values less than 1 are rejected. |
| `per_page` | integer (default varies per endpoint, capped) | Number of items to return per page. Values greater than the endpoint cap are clamped to the cap. |
| `sort` | string | Format `field:direction`. `direction` must be `asc` or `desc`. Each endpoint publishes a whitelist of sortable fields; anything else returns validation error `422`. |
| `filter[*]` | varies | Filtering uses a namespaced array syntax. Example: `filter[status]=active`. Field names and supported values are endpoint-specific. Empty filter values are ignored. |

### Example

```
GET /api/products?page=2&per_page=30&sort=created_at:desc&filter[brand]=acme
```

## Response envelope

Every successful list response returns the following structure:

```json
{
  "data": [ /* array of items */ ],
  "meta": {
    "page": 2,
    "per_page": 30,
    "total": 150,
    "total_pages": 5
  },
  "links": {
    "next": "https://example.test/api/products?page=3&per_page=30",
    "prev": "https://example.test/api/products?page=1&per_page=30"
  },
  "context": { /* optional endpoint-specific payload */ }
}
```

- `links.next` and `links.prev` are `null` when there is no subsequent/previous page.
- Additional contextual keys (for example `context.product`) may be included when an endpoint needs to return related metadata.

## Error handling

- Requests with invalid `sort` fields or directions respond with HTTP `422` and a validation message.
- Requests missing required filters return HTTP `400` with an explanatory message.
- `per_page` values above the configured cap are automatically reduced to the cap and reflected in `meta.per_page`.

## Testing expectations

Feature coverage asserts:

1. `per_page` values above the cap fall back to the configured maximum.
2. Invalid `sort` parameters yield a validation error.
3. Empty datasets return an empty `data` array with the correct pagination metadata.
4. Navigating to the final page produces `links.next = null` and the correct `meta.total_pages`.
