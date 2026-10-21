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


## History and Web Push
- `GET /api/v1/history?limit=30` returns `data.war_snapshots` and `data.planet_snapshots`; limit is clamped to 1–100.
- `GET /api/v1/push/public-key` returns the configured public VAPID key or `503 push_not_configured`.
- `POST /api/v1/push/subscribe` accepts `{ "subscription": { "endpoint": "https://...", "keys": { "p256dh": "...", "auth": "..." } }, "preferences": { "orders": true, "dispatches": true, "attacks": true } }`. Preferences are optional and default to enabled.
- `DELETE /api/v1/push/subscribe` accepts `{ "endpoint": "https://..." }`.
- Push sending is CLI-only: `bin/check-alerts.php` compares persisted notification state, and `bin/push.php` sends a manual test notification. Both require `composer install` and VAPID configuration.
