# Changelog

## DiverSignal companion refocus
- Reworked the SPA around Orders, Loadout Advisor, Combat Intel, Order Details and More.
- Removed the galaxy map and planet theatre from the visible interface and stopped requesting planet data during the main page load.
- Added one concise line per available Supreme Order task/objective, with a click-through to full order details.
- Added mission type and combat style filters to loadout recommendations.
- Added tactical guidance for Terminids, Automatons, Illuminate and common mission types.
- Added English, Simplified Chinese, Russian, Spanish, Portuguese and German UI dictionaries with cookie/local-storage language persistence.
- Added About DiverSignal, donation placeholder and banner/rewarded-ad placement placeholders.
- Added opt-in browser push subscription endpoints and order-only notification preferences.
- Restricted scheduled push checks to new Supreme Orders and recognized terminal/expired states; removed dispatch and planet-attack push events.
- Improved notification click handling so relative links open the SPA route within the service worker's scope.
- Added `push_subscriptions` and `notification_state` tables to the schema.
- Removed malformed HTML-like tags from upstream text before displaying it.
- Updated deployment and smoke-test documentation.

## Rendering and loadout image fix
- Normalize malformed HTML-like game tags and render common Steam BBCode paragraphs, lists, formatting, and HTTP(S) links safely.
- Add visual stratagem cards with category artwork from Helldivers Wiki.gg, emoji fallback, and a source link for each recommendation.


## Loadout stratagem-only update
- Replaced mixed weapon/armor recommendations in the visual advisor with stratagem-only choices.
- Cards use the stratagem name as the title and show the call-in arrow sequence as the subtitle.
- Removed fuzzy wiki search image matching; images are requested only from the exact stratagem wiki page, with a safe icon fallback.
