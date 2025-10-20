# API Listing Contract

The shared API listing contract standardises pagination, sorting, and filtering across controllers that expose collection endpoints. All API list endpoints that leverage `App\Support\ListQuery` now adhere to the same query parameter semantics and response payload shape.

## Query Parameters

Every listing endpoint accepts the following query parameters:

| Parameter   | Type    | Description |
|-------------|---------|-------------|
| `page`      | int     | 1-based page number. Defaults to `1`. |
| `per_page`  | int     | Number of results per page. Defaults to the definition's `defaultPerPage` and is clamped to its `maxPerPage`. |
| `sort_by`   | string  | Logical sort key defined by the controller's `ListQueryDefinition`. Invalid values trigger a `422` response. |
| `sort_dir`  | string  | Sort direction (`asc` or `desc`). Defaults to the definition's direction. |
| Filters     | mixed   | Each controller registers allowed filters via `ListQueryDefinition::filters()`. Values are validated, type-cast, and exposed in the response metadata. |

Unknown parameters are ignored. Controllers can express complex behaviour (e.g., relation scopes, LIKE searches, date ranges) through custom callbacks in their definitions.

## Response Payload

Successful responses share the following JSON shape:

```json
{
  "success": true,
  "data": [...],
  "meta": {
    "pagination": {
      "total": 123,
      "count": 15,
      "per_page": 15,
      "current_page": 1,
      "last_page": 9,
      "from": 1,
      "to": 15
    },
    "sort": {
      "by": "clicked_at",
      "direction": "desc"
    },
    "filters": {
      "is_converted": true,
      "date_from": "2024-01-01"
    }
  },
  "links": {
    "first": "https://example.test/campaign-clicks?page=1",
    "last": "https://example.test/campaign-clicks?page=9",
    "prev": null,
    "next": "https://example.test/campaign-clicks?page=2"
  }
}
```

Controllers may append contextual metadata (e.g., user-centric timestamps or domain-specific payloads) alongside the shared `meta` block when necessary.

## Implementation Notes

* `ListQueryDefinition` encapsulates the allowed sorts, filters, and pagination limits for a controller.
* `ListQueryValidator::fromRequest()` validates and type-casts the incoming query parameters.
* `ListQuery::apply()` applies the validated filters and sorting to an Eloquent query builder and returns a paginator.
* `ListResponse::fromPaginator()` transforms paginator results into the standard response structure while preserving active filter values for debugging and front-end state hydration.

See the updated API controllers (CampaignClick, Category, Product, ProductHistory, Notification, and User) for concrete definition examples.
