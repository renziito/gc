# Patch notes — based on your supplied ZIP

This package was patched in-place from `DiverSignal_UI_Fixes(2).zip`, keeping its existing design.

## Fixes
- Major Order descriptions now render safe nested BBCode (`[b]`, `[i]`, `[u]`, `[s]`) using the same renderer used by news/dispatches.
- Major Order cards can be selected to open a detail dialog.
- The dialog shows briefing, objectives/progress, rewards, expiry/status, and any extra fields present in the API payload.
- Dialog labels follow the current English/Spanish language preference.

## Upload
1. Back up the current folder.
2. Upload/overwrite the files from this ZIP, preserving the directory structure.
3. Do not overwrite `config/config.php`: it is omitted because the ZIP supplied to me contained database and push credentials. Keep your server's current configuration.
4. Clear browser cache/service worker if old CSS or JS remains cached.

## Remaining limitations
- This archive currently implements English and Spanish UI labels; it does not contain complete Simplified Chinese, Russian, Portuguese and German translations.
- The source ZIP contains no official individual stratagem artwork, so no unverified remote image URLs were fabricated.
- Order details can display only fields present in the synchronized API payload. If the upstream source does not provide a field, the dialog says so rather than inventing data.
