# Partner API Contract

The Partner API provides certified integrators with secure access to merchandising, inventory, and order management capabilities. This contract describes the authentication model, scope boundaries, rate limiting guarantees, onboarding lifecycle, and observability mechanics required to integrate successfully.

## Authentication Model

Partners authenticate every request with an API key tied to their organization.

- **Header usage**: Include the header `X-Partner-Key: <api_key>` on every request. Omit the header only on pre-flight `OPTIONS` calls handled by the gateway.
- **Key lifecycle**:
  - Keys are generated per integration environment (sandbox, staging, production) and are scoped to a single partner account.
  - Keys expire automatically every 12 months. Rotation reminders are emailed 30 days prior to expiry. Rotate keys by requesting a new key, validating it in sandbox, then promoting it to production.
  - Compromised keys must be revoked immediately via the Partner Console or by contacting support (see [Observability & Support](#observability--support)). Revocation takes effect within 60 seconds across all edges.
  - Delete unused keys to reduce attack surface; keys inactive for 90 days are disabled automatically.

Requests without a valid `X-Partner-Key` header receive a `401 Unauthorized` response (see [Error Handling](#error-handling)).

## Available Scopes

Each key is issued with one or more scopes that govern accessible API surfaces. The `X-Partner-Key` encodes scope membership and does not require an extra header.

| Scope | Description | Example Endpoints |
| --- | --- | --- |
| `catalog:read` | Read product catalog data. | `GET /v1/catalog/items`, `GET /v1/catalog/items/{id}` |
| `catalog:write` | Create or update catalog items. Requires `catalog:read`. | `POST /v1/catalog/items`, `PATCH /v1/catalog/items/{id}` |
| `inventory:manage` | Adjust stock counts and reservations. | `POST /v1/inventory/adjustments`, `GET /v1/inventory/locations` |
| `orders:read` | Retrieve orders and fulfillment status. | `GET /v1/orders`, `GET /v1/orders/{id}` |
| `orders:write` | Create or update partner-originated orders. Requires `orders:read`. | `POST /v1/orders`, `PATCH /v1/orders/{id}` |
| `webhooks:manage` | Configure webhook subscriptions. | `POST /v1/webhooks`, `DELETE /v1/webhooks/{id}` |

Scopes are additive; requests to endpoints outside of the authorized scopes return `403 Forbidden`.

## Rate Limiting

Rate limits are enforced per API key and vary by environment. Production limits are cumulative across all regions.

| Environment | Burst Limit | Sustained (per minute) | Daily Ceiling |
| --- | --- | --- | --- |
| Sandbox | 20 requests / second | 600 | 50,000 |
| Staging | 40 requests / second | 1,200 | 120,000 |
| Production | 60 requests / second | 1,800 | 250,000 |

Exceeding a limit yields `429 Too Many Requests` responses with retry guidance (see [Error Handling](#error-handling)).

## Example Requests and Responses

### Fetch Catalog Item

**Request**

```http
GET /v1/catalog/items/sku-123 HTTP/1.1
Host: api.partner.example.com
X-Partner-Key: {{production_key}}
Accept: application/json
```

**Successful Response**

```json
{
  "id": "sku-123",
  "name": "Signature Hoodie",
  "description": "Unisex fleece hoodie in charcoal",
  "price": {
    "amount": "59.00",
    "currency": "EUR"
  },
  "inventory": {
    "available": 128,
    "reserved": 6
  },
  "updated_at": "2024-04-02T14:23:51Z"
}
```

### Create Inventory Adjustment

**Request**

```http
POST /v1/inventory/adjustments HTTP/1.1
Host: api.partner.example.com
Content-Type: application/json
X-Partner-Key: {{production_key}}

{
  "location_id": "vilnius-dc-1",
  "sku": "sku-123",
  "delta": -4,
  "reason": "damaged during transit"
}
```

**Successful Response**

```json
{
  "id": "adj-987654",
  "status": "applied",
  "applied_at": "2024-04-03T09:11:07Z",
  "location_id": "vilnius-dc-1",
  "sku": "sku-123",
  "delta": -4,
  "running_total": 124
}
```

## Onboarding & Key Management

1. **Request access**: Submit the onboarding form in the Partner Console with company details, data residency requirements, and desired scopes per environment. Approval typically occurs within 3 business days.
2. **Receive keys**: Sandbox keys are issued first. Validate integrations against sandbox endpoints before requesting staging or production keys.
3. **Promote to production**: Demonstrate compliance with the certification checklist (security scan, data handling review, error budget alignment). After approval, production keys are provisioned.
4. **Rotation**: Schedule key rotation at least every 6 months. Use overlapping validity periods to deploy new keys without downtime.
5. **Revocation**: Partners can revoke keys through the console. For emergency revocation, email `security@prus.dev` or call the 24/7 hotline. Revoked keys cannot be reactivated; request new keys as needed.

## Error Handling

All error responses include a JSON body containing a stable machine code and human-readable message.

### 401 Unauthorized

Returned when the `X-Partner-Key` header is missing, invalid, or expired.

```json
{
  "error": "unauthorized",
  "message": "Missing or invalid partner API key.",
  "remediation": "Provide a valid X-Partner-Key header and retry."
}
```

### 403 Forbidden

Returned when the authenticated key lacks the required scope or the partner account is suspended.

```json
{
  "error": "forbidden",
  "message": "Your key is not authorized for catalog:write scope.",
  "remediation": "Request scope access in the Partner Console or contact support."
}
```

### 429 Too Many Requests

Returned when rate limits are exceeded.

```json
{
  "error": "rate_limited",
  "message": "Rate limit exceeded for this key.",
  "remediation": "Wait for the window to reset or request a higher quota."
}
```

Every error includes a unique `request_id` header correlating with logs; include it when opening support tickets.

## Observability & Support

- **Rate-limit headers**: All responses include `X-RateLimit-Limit`, `X-RateLimit-Remaining`, and `X-RateLimit-Reset` headers to aid client-side throttling. `X-RateLimit-Reset` is a UNIX timestamp indicating the next reset.
- **Tracing**: Provide the `X-Request-ID` response header in any support communication. Optional `X-Correlation-ID` headers in requests are echoed in responses for distributed tracing.
- **Audit logs**: Partner Console exposes a 30-day rolling log of key usage and revocation events. Export logs in CSV or syslog formats.
- **Support contacts**:
  - General inquiries: `partners@prus.dev` (business hours 08:00–18:00 EET).
  - Priority incidents: `support@prus.dev` (24/7, response SLA 1 hour).
  - Security emergencies: `security@prus.dev` or hotline `+370 800 12345`.

For additional analytics, enable the optional webhook subscription `partner.rate-limit.near-threshold` to receive proactive notifications when consumption reaches 80% of the sustained per-minute quota.

