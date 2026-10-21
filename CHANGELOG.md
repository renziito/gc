

## Fixes from the latest UI review

- Added safe BBCode rendering for `[b]`, `[i]`, `[u]`, `[s]`, line breaks and list bullets in dispatch/news body text.
- Normalized malformed HTML-like tags in upstream text before display.
- Improved loadout recommendation cards with tactical visual tiles, clearer hierarchy, and a contextual armor-passive tip.
- Unified saved language preference between cookie/local storage where the existing interface supports it.
- Removed `.git` metadata from the deployment package.

## Patch — preserve existing UI and fix order details/content rendering
- Used the supplied `DiverSignal_UI_Fixes(2).zip` as the source project; no visual redesign or replacement theme.
- Major Order cards now open a details modal populated from the current synchronized order payload.
- Major Order summaries and details render supported BBCode emphasis safely instead of stripping formatting.
- Order cards expose keyboard activation and the modal closes with Escape or its close control.
- Improved the shared client-side rich-text parser for paragraph breaks and BBCode URL labels.
- Existing loadout layout and armor-tip component were preserved.
