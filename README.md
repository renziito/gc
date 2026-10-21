# Galactic Command / DiverSignal — Backend v1

This version uses the real JSON structures captured in `galactic-json.zip`. It intentionally stores complete upstream payloads as JSON alongside normalized relational records. This avoids losing source fields while we develop stable app DTOs.

## Required environment
- PHP 8.1+ (8.2 preferred), extensions `curl`, `pdo_mysql`, `json`, `mbstring`
- MySQL 8.0+ or compatible MariaDB with JSON support
- HTTPS
- A cron facility

## Deploy

The distributable source ZIP intentionally omits `config/config.php` because it contains deployment-specific credentials. Copy `config/config.example.php` to `config/config.php` and re-enter your own database settings; do not commit the live config or runtime logs.
1. Create a dedicated MySQL database and user in cPanel.
2. Import `database/schema.sql`.
3. Copy `config/config.example.php` to `config/config.php`; fill credentials and set a random `app_key`.
4. Keep `app/`, `config/`, `database/`, `bin/`, and `storage/` outside the web document root. Set document root to `public/`. If hosting cannot do that, deny access to those folders explicitly.
5. Set `GC_CRON_TOKEN` in your environment if possible, or configure the same long random token in `config.php`.
6. Upload files and test `GET /api/v1/health`.
7. Test `GET /` for the endpoint directory and `GET /api/v1/health` for health. Supported aliases include `/api/v1/assignments` and `/api/v1/orders`, `/api/v1/dispatches` and `/api/v1/news`, plus `/api/v1/steam` and `/api/v1/steam-news`.
8. Run a one-time sync manually from CLI: `php /path/to/diversignal/bin/sync.php --source=war`, then `--source=planets`, `--source=planet_events`, `--source=assignments`, `--source=campaigns`, `--source=dispatches`, `--source=space_stations`, `--source=steam_news`.
9. The individual station endpoint must use `id32`, not the array `index` nor a DB auto-increment id: `php bin/sync.php --source=space_station_by_index --id32=749875195`.
10. Verify `GET /api/v1/war`, `/api/v1/planets`, `/api/v1/orders`, `/api/v1/news`, `/api/v1/space-stations`.
11. Only after manual runs are successful, schedule `php bin/sync.php --due` every 2 minutes. The scheduler honors per-source intervals, but upstream rate limits still need monitoring.

## Important route facts from captured JSON
- `war`: `/api/v1/war` -> object containing `started`, `ended`, `now`, `clientVersion`, `factions`, `impactMultiplier`, `statistics`.
- `planets`: `/api/v1/planets` -> array (273 items in captured sample) with `index`, `name`, `sector`, `biome`, `hazards`, `position`, `maxHealth`, `health`, `currentOwner`, `regenPerSecond`, `event`, `statistics`, `attacking`, `regions`.
- `planet_events`: `/api/v1/planet-events` -> array of planet records with a non-null `event` in the captured sample.
- `assignments`: `/api/v1/assignments` -> array containing `id`, `progress`, `title`, `briefing`, `description`, `tasks`, `reward`, `rewards`, `expiration`, `flags`.
- `campaigns`: `/api/v1/campaigns` -> array of campaign records with `id`, nested `planet`, `type`, `count`, `faction`.
- `dispatches`: `/api/v1/dispatches` -> array with `id`, `published`, `type`, `message`.
- `space_stations`: `/api/v1/space-stations` -> array with `id32`, nested `planet`, `electionEnd`, `flags`, `tacticalActions`.
- `space_station_by_index`: `/api/v2/space-stations/{id32}`. Use the station **id32** (example `749875195`), never its array index or local DB id. The validator capture returned HTTP 200 for that ID.
- `steam_news`: `/api/v1/steam` -> array with `id`, `title`, `url`, `author`, `content`, `publishedAt`.

All counts above are from the supplied capture and can change. HTTP 200 confirms transport success at capture time, not a permanent upstream contract.

## Sync design
- Raw response JSON is saved for traceability.
- Planet, assignment, campaign, dispatch and station tables are normalized conservatively from observed fields.
- `war` stores a raw snapshot and selected summary values.
- Source sync failures are logged and never delete last known good payloads.
- The DB-backed advisory lock serializes upstream requests from this app.
- The `--due` scheduler checks each source interval before fetching.
- Do not expose `bin/sync.php` as a web endpoint. Run it with CLI cron only.


## Companion modules included in this package

- **War Room:** live war/planet feed, planetary search and schematic map, active orders, campaigns, dispatches, Steam news, and station summary.
- **Combat intelligence:** mission-context loadout planner for faction, mission type, playstyle, difficulty, squad size, and optional planet. Recommendations use a transparent tag-based heuristic and are not presented as live win-rate statistics.
- **Community intel:** operational snapshot derived from synchronized planet/campaign feeds (tracked planets, attacked planets, event planets, reported campaigns).
- **Galactic Archives:** searchable timeline combining current dispatches, official updates, orders, and active planetary events. `/api/v1/history?limit=30` exposes persisted war and planet snapshots.
- **Field Manual / Codex:** searchable starter catalog of stratagems, weapons, armor archetypes, and enemies. It is a curated reference, not a live unlock/balance feed.
- **Localization:** English/Spanish dashboard toggle. Canonical in-game equipment names remain in English where that avoids ambiguous translations.
- **Alerts:** local browser notifications while the dashboard is open, plus optional Web Push delivery when configured. Local alerts work without VAPID setup.

### Enable Web Push (optional)

1. Run `composer install` in the project root.
2. Generate a VAPID key pair on the server:
   ```bash
   php -r "require 'vendor/autoload.php'; print_r(Minishlink\\WebPush\\VAPID::createVapidKeys());"
   ```
3. Copy the generated `publicKey` and `privateKey` into `config/config.php` under `push.public_key` and `push.private_key`. Keep the private key secret. Set `push.subject` to a contact URI such as `mailto:ops@example.com`.
4. Apply the updated `database/schema.sql` to create the `push_subscriptions` and `notification_state` tables. If `push_subscriptions` already exists from an earlier deployment, also run `ALTER TABLE push_subscriptions ADD COLUMN preferences_json JSON NULL;`.
5. Open the site over HTTPS and click **Enable alerts**. The browser asks permission, registers `public/sw.js`, and stores its push subscription through `POST /api/v1/push/subscribe`.
6. Add this CLI job to the existing scheduler, ideally every 2 minutes after `bin/sync.php --due`:
   ```bash
   php /path/to/gc/bin/check-alerts.php
   ```
   It deduplicates major-order identity changes, new dispatches, and newly attacked planets. Expired push subscriptions are removed when delivery reports them.
7. Optional manual test:
   ```bash
   php /path/to/gc/bin/push.php --title="Galactic Command test" --body="Push delivery is configured."
   ```

Web Push requires HTTPS (localhost is accepted by browsers for development), a valid VAPID key pair, the Composer dependency, and the new schema tables. Without these steps, the dashboard still supports local browser alerts, but it cannot notify a closed browser.

### New API endpoints

- `GET /api/v1/history?limit=30` — latest stored war and planet snapshots; `limit` is clamped to 1–100.
- `GET /api/v1/push/public-key` — public VAPID key (503 if push is not configured).
- `POST /api/v1/push/subscribe` — save a browser push subscription.
- `DELETE /api/v1/push/subscribe` — remove a subscription by endpoint.

The subscription endpoint only accepts HTTPS push service hosts supported by major browsers and stores each subscriber's order/dispatch/attack preferences. Keep `config/config.php`, private VAPID keys, database credentials, `vendor/`, and operational logs out of public web access.
