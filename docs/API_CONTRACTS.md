# API v1 contracts

All endpoints return UTF-8 JSON in an envelope:
`{"ok":true,"data":...,"meta":{"generated_at":"...","source_key":"...","source_fetched_at":"..."}}`

## Read endpoints
- `GET /api/v1/health`
- `GET /api/v1/war`
- `GET /api/v1/planets`
- `GET /api/v1/planets/{index}` — normalized planet row's raw source object
- `GET /api/v1/planet-events`
- `GET /api/v1/orders`
- `GET /api/v1/campaigns`
- `GET /api/v1/news` — war dispatches
- `GET /api/v1/steam-news`
- `GET /api/v1/space-stations`
- `GET /api/v1/space-stations/{id32}` — detail captured using the station's `id32`.

## Initial caveat
The list endpoints return the latest raw upstream JSON payload inside `data` to make transport and caching work first. Relational tables are also populated for the fields seen in the supplied validator JSON. Before locking Android DTOs, add normalized DTO envelopes and API contract tests. This avoids pretending all upstream semantics have been conclusively documented.


## Push notification routes
- `GET /api/v1/push/key` — returns the public VAPID key for browser subscription.
- `POST /api/v1/push/subscribe` — stores a validated browser subscription with the `orders` preference enabled.
- `POST /api/v1/push/unsubscribe` — removes a subscription by its endpoint.

The scheduled notification checker is intentionally limited to new Supreme Orders and recognized completion/expiration of the current order. Dispatches, planet attacks, and ordinary progress changes do not send notifications.
