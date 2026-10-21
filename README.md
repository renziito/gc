# DiverSignal — Helldivers 2 companion

DiverSignal is a lightweight, single-page companion intended to stay open beside Helldivers 2. It focuses on current Supreme Order missions, loadout suggestions, and general Combat Intel. It deliberately does **not** reproduce the in-game galactic war map.

## Current application sections

- **Orders:** the current Supreme Order summary and one concise line per mission/objective.
- **Loadout Advisor:** enemy faction, mission type, combat style, and difficulty filters.
- **Combat Intel:** general recommendations against Terminids, Automatons, and Illuminate, plus advice by mission type.
- **Order Details:** full order description, objectives, progress and deadline where the upstream feed provides them.
- **More:** About DiverSignal, donation placeholder, notification setup, ad placement placeholders, dispatches, official news, station information and community notes.
- Six interface languages: English, Simplified Chinese, Russian, Spanish, Portuguese, and German. The language preference is stored in a cookie and local storage.
- Single-page navigation: section changes do not reload the entire page.
- Text displayed from upstream feeds is normalized to remove HTML-like tags such as malformed `<i=3>` before it is escaped for display.

The loadout recommendations are heuristic guidance, not official win-rate statistics. Verify current balance and unlocks in-game.

## Hosting requirements

- PHP 8.1+ (PHP 8.2 preferred)
- PHP extensions: `curl`, `pdo_mysql`, `json`, `mbstring`
- MySQL 8+ or compatible MariaDB with JSON support
- HTTPS is required for browser push notifications and service workers
- Composer dependencies installed from `composer.json`
- Cron access for data synchronization and order-event notification checks

## Upload to cPanel or a PHP host

1. Back up the existing application and database before replacing files.
2. Upload the project files. Prefer setting the domain/subdomain document root to the `public/` folder. Keep `app/`, `bin/`, `config/`, `database/`, and `storage/` outside the public document root.
3. Preserve your existing `config/config.php` if it contains your live database credentials, app key, push VAPID keys, or other host-specific values. Use `config/config.example.php` as a reference; do not overwrite a working production config with example placeholders.
4. Import `database/schema.sql`. It creates the data tables and the `push_subscriptions` and `notification_state` tables used by order notifications. Existing databases can import it again because the tables use `IF NOT EXISTS`.
5. Install Composer dependencies in the project root: `composer install --no-dev --optimize-autoloader`.
6. Test `GET /api/v1/health`; it should return JSON with `"status":"up"`.
7. Run a manual data sync from the command line, for example:
   - `php bin/sync.php --source=assignments`
   - `php bin/sync.php --source=dispatches`
   - `php bin/sync.php --source=steam_news`
   - `php bin/sync.php --source=space_stations`
8. After verifying the feeds, schedule `php /absolute/path/to/bin/sync.php --due` every two minutes, adjusting the absolute path for your hosting account.
9. Schedule `php /absolute/path/to/bin/check-alerts.php` every two minutes. It sends push notifications only for a newly detected Supreme Order or when the current order reaches a recognized terminal state. The initial state is stored without sending a notification.
10. Enable notifications from **More → Order notifications** on each browser/device. HTTPS and valid VAPID keys are required.

## Push notification behavior

- Push is restricted to the `orders` topic.
- The application does not send notifications for dispatches, official news, planet attacks, or ordinary progress polling.
- Notification links use a relative path (`./#orders` or `./#details`) and the service worker resolves them against its registration scope, so deployments in a subdirectory can open the corresponding SPA view.
- Native Android/iOS app links still require a separately built mobile app and platform association files. This package contains the web application and web push implementation, not a native Android application.

## Advertising and donations

The banner and optional rewarded-ad spaces are placeholders only. No advertising provider or reward is active. The donation link is intentionally a placeholder until a destination is supplied.

## API routes

The current web API includes `/api/v1/health`, `/api/v1/war`, `/api/v1/planets`, `/api/v1/assignments`, `/api/v1/orders`, `/api/v1/campaigns`, `/api/v1/dispatches`, `/api/v1/news`, `/api/v1/steam`, `/api/v1/steam-news`, `/api/v1/space-stations`, and `/api/v1/planet-events`.

Push routes:
- `GET /api/v1/push/key`
- `POST /api/v1/push/subscribe`
- `POST /api/v1/push/unsubscribe`

Do not expose CLI scripts such as `bin/sync.php` as web endpoints.

## Package notes

- Git metadata, GitHub workflows, generated error logs and cron logs are excluded from the deployment ZIP.
- The ZIP preserves the existing application files and configuration as supplied in the current project archive. Treat `config/config.php` as sensitive because it contains server-specific credentials and keys.

## Latest patch notes

This package preserves the supplied visual design. Dispatch/news text now renders common BBCode formatting (`[b]`, `[i]`, `[u]`, `[s]`, and URL tags) safely. Order parsing accepts common nested response shapes and additional deadline/objective field aliases. Changing the language re-renders currently cached feeds. Loadout recommendations have more structured cards and armor tips.

Official individual stratagem images were not present in the supplied archive, so this patch does not claim to include them.
