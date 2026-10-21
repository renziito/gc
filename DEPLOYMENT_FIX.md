# DiverSignal deployment notes

- Use the `public/` directory as the document root when possible.
- Keep `app/`, `bin/`, `config/`, `database/`, and `storage/` outside the public web root.
- Preserve the production `config/config.php`; do not replace it with example values.
- Import `database/schema.sql` to create missing tables, including push subscription and notification state storage.
- Install dependencies with `composer install --no-dev --optimize-autoloader`.
- Use HTTPS for service workers and push notifications.
- Schedule `bin/sync.php --due` and `bin/check-alerts.php` via CLI cron.
- Push notification checks only announce a newly detected Supreme Order or a recognized end/completion of the current order.
