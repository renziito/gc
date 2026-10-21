# Galactic Command smoke-test checklist

Run these checks after deploying to a staging database.

1. `php -l public/index.php`
2. `php -l app/PushNotifier.php`
3. `node --check public/assets/modules.js`
4. Import the updated `database/schema.sql` (or execute its two new `CREATE TABLE IF NOT EXISTS` statements).
5. Run `php bin/sync.php --source=war`, `--source=planets`, `--source=assignments`, `--source=campaigns`, `--source=dispatches`, `--source=steam_news`, and `--source=space_stations`.
6. Verify `GET /api/v1/health`, `/api/v1/war`, `/api/v1/planets`, `/api/v1/history?limit=10`.
7. Confirm the dashboard shows the planet map/list, orders, campaigns, dispatches, official news, loadout planner, community snapshot, archive timeline, and searchable codex.
8. Change EN/ES and confirm labels change without losing the chosen loadout settings.
9. With HTTPS, enable browser notifications. If VAPID is configured, confirm a subscription row appears in `push_subscriptions`.
10. Run `php bin/check-alerts.php` twice with no source changes; the second run should not resend the same event.
11. Confirm a test notification using `php bin/push.php --title="Test" --body="Galactic Command push test"`.
12. Test mobile width, keyboard focus, and browser console for errors.

The source repository does not include automated end-to-end browser tests or live upstream fixtures. Verify data shapes against the current upstream API before relying on operational recommendations.
