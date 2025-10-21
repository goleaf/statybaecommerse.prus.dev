# Listing Contract

The listing contract standardises how pagination, sorting, and filtering are handled inside the API layer. Controllers build a
`ListQueryDefinition`, validate the incoming request with `ListQueryValidator`, and use the resulting `ListQuery` instance to
mutate the underlying Eloquent builder before returning a response that is wrapped by `ListResponse`.

## Request semantics

Every listing endpoint accepts the following shared parameters:

| Parameter | Description |
| --- | --- |
| `page` | 1-indexed current page. Defaults to `1` and is clamped to the minimum configured for the definition. |
| `per_page` | Number of items per page. Each definition provides sensible defaults and an upper bound that protects the
application from unexpectedly large payloads. |
| `sort` | Comma separated sort string (e.g. `sort=-created_at,name`). Prefix an attribute with `-` to sort in descending order.
Legacy `sort_by` and `sort_order` pairs are still honoured by the validator. |
| `filters[...]` | Optional nested filter values. Definitions also allow top-level parameters when migration is not yet complete. |

Each controller declares which filters and sort keys are available. The validator coerces native types (`int`, `bool`,
`datetime`, etc.), rejects unknown fields, and enforces `in` lists when supplied.

## Applying the query

The `ListQuery` object exposes helpers that apply the sanitised filters and sort instructions to any `Builder` instance:

```php
$listQuery = ListQueryValidator::fromRequest($request, $definition);

$query = CampaignClick::query();
$listQuery->applyFilters($query);
$listQuery->applySorts($query);

$results = $query->paginate(
    $listQuery->perPage(),
    ['*'],
    'page',
    $listQuery->page(),
);
```

Filters can define callbacks for complex clauses and default operators for standard equality comparisons. Sorts store both the
original request key and the resolved column so controllers can inspect which sort was applied with `hasSort()`.

## Response payload

`ListResponse` assembles consistent metadata for each listing payload:

- `meta.query` surfaces the current page, page size, applied filters, and sorts.
- `meta.pagination` mirrors the paginator state (current/last page, total rows, cursor window).
- `links` contains first/last/prev/next URLs where relevant.

Controllers either pass the `meta` array into a contract helper (e.g. `CategoryContract`) or return the structure directly when
responding with JSON resources. This keeps pagination affordances in sync across the API.

## Extending

Definitions live alongside the controllers that consume them. When adding a new list endpoint:

1. Identify the allowed filters, sort keys, defaults, and maximum page size.
2. Build a `ListQueryDefinition` and validate the incoming request with `ListQueryValidator`.
3. Apply the returned `ListQuery` to the query builder.
4. Use `ListResponse::meta()` or `ListResponse::fromPaginator()` to provide consumers with consistent metadata.

Tests that exercise listing behaviour should assert against `meta.query` to confirm edge cases such as capped `per_page`
values or sanitised sorts.
