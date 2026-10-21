# Deployment fix: PHP Normalizer class conflict

The project class has been renamed from `Normalizer` to `DataNormalizer` because PHP's intl extension may already define the global class `Normalizer`.

Files updated:
- `app/Normalizer.php` renamed to `app/DataNormalizer.php`
- `app/bootstrap.php` now loads `DataNormalizer.php`
- `app/SyncService.php` now calls `DataNormalizer::...`

## Deploy
1. Upload/extract all files from this package into your `gc/` directory, replacing the old project files.
2. Keep your private `config/config.php` credentials in place. Do not overwrite it with the example file unless you then set your real database credentials and secrets.
3. If you are configuring the project for the first time, copy `config/config.example.php` to `config/config.php` and fill in your actual values.
4. Run PHP syntax checks if shell access is available:

```bash
find /home/ynrttgii/public_html/gc -name '*.php' -print0 | xargs -0 -n1 php -l
```

5. Make sure the deployed directory no longer contains `app/Normalizer.php`; it should contain `app/DataNormalizer.php`.

This package does not include production database passwords or secret tokens.
