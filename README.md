# Galactic Command / DiverSignal — Backend v1

This version uses the real JSON structures captured in `galactic-json.zip`. It intentionally stores complete upstream payloads as JSON alongside normalized relational records. This avoids losing source fields while we develop stable app DTOs.

## Required environment
- PHP 8.1+ (8.2 preferred), extensions `curl`, `pdo_mysql`, `json`, `mbstring`
- MySQL 8.0+ or compatible MariaDB with JSON support
- HTTPS
- A cron facility

## Deploy
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


## Preservation-first UI update
- Restyles the existing Galactic Command interface with a Helldivers-inspired tactical black/yellow visual system.
- Keeps the original planetary galaxy map, planet filters, orders, campaigns, dispatches, Steam news, station panel, and existing API/rendering logic in place.
- Adds compact incoming-message rectangles at the top, populated from current orders, dispatches, and official news.
- Adds a current Supreme Order banner on the main screen.
- Adds EN/ES language toggle for the refreshed interface labels.
- Adds a heuristic loadout suggestion panel for faction, role, and difficulty. It is a field-guide recommendation, not live win-rate data.
- Deployment note: preserve the existing server's `config/config.php`; it is intentionally excluded from this ZIP to avoid overwriting server-specific credentials/settings. Operational logs and `.git` metadata are also excluded.


## SPA navigation and API loading repair
- Added four in-place views (Command, Galactic Theatre, Combat Intelligence, Dispatches & Archives). Navigation swaps visible views without full-page reloads or long-page scrolling.
- Preserved the existing galaxy map and its data renderers, orders/campaigns, dispatches/news and station panel.
- Fixed a JavaScript initialization-order bug: the loadout renderer was called before the shared HTML-escaping helper (`esc`) was initialized, which could abort the entire script and leave every panel stuck on Loading.
- War and planet feed failures now display explicit API error messages instead of remaining indefinitely in a loading state.
- Keep the server's existing `config/config.php`; it is intentionally not included in this ZIP.


## Patch based on the supplied `DiverSignal_UI_Fixes(2).zip`
- Preserves this archive's existing dark, yellow-accented tactical interface and SPA views; this is not a redesign/revert.
- Major Order cards are now keyboard-accessible buttons. Selecting one opens an in-place detail dialog with briefing, objectives/progress, rewards, expiry/status and other fields available in the synchronized source payload.
- Order briefings use the same safe BBCode renderer as dispatches/news, so nested `[b]`, `[i]`, `[u]`, `[s]` formatting renders as emphasis rather than literal tags.
- The order detail dialog labels and empty-state messages follow the existing English/Spanish UI preference.
- This patch does not add the four missing languages (Simplified Chinese, Russian, Portuguese and German), and this archive has no official stratagem artwork files. The existing loadout presentation is preserved rather than inserting guessed image URLs.
- Keep the current server's `config/config.php` and do not overwrite it from this ZIP; it is intentionally omitted from this patch package. The supplied archive contained credentials in that file.
