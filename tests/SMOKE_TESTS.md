# DiverSignal smoke-test checklist

Run these checks on a staging deployment before replacing production files.

1. `php -l public/index.php`
2. `php -l bin/check-alerts.php`
3. `php -l app/PushNotifier.php`
4. `node --check public/sw.js`; extract the inline script from `public/index.php` and run `node --check` on it.
5. Import `database/schema.sql`; confirm `push_subscriptions` and `notification_state` exist.
6. Verify `GET /api/v1/health`, `/api/v1/assignments`, `/api/v1/orders`, `/api/v1/dispatches`, `/api/v1/steam`, and `/api/v1/space-stations`.
7. Confirm the initial Orders view shows the Supreme Order and one concise line per task/objective. There must be no galaxy map in the interface.
8. Open a mission row and confirm Order Details displays the order description, objectives, progress and deadline when the upstream feed supplies them.
9. Test the Loadout Advisor with all three factions, mission types, combat styles and difficulty settings.
10. Change between English, Simplified Chinese, Russian, Spanish, Portuguese and German. Reload the page and confirm the selected language persists.
11. Confirm malformed upstream HTML-like tags such as `<i=3>` are not displayed.
12. On HTTPS, enable order notifications from More. Confirm a subscription is stored in `push_subscriptions`.
13. Run `php bin/check-alerts.php` twice without changing the order payload; the second run should not resend a notification. Verify dispatches and planetary events do not trigger notifications.
14. Test mobile widths, keyboard focus, internal navigation and browser console errors.

Push delivery depends on valid VAPID keys, Composer dependencies, browser permission, HTTPS, and the scheduled CLI check. Native Android app links require a separate native app and platform association configuration.
