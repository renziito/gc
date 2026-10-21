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

## UI and content fixes

- Preserved the supplied DiverSignal visual design and existing PHP SPA structure.
- Added safe rendering for common upstream BBCode tags in dispatch/news descriptions.
- Expanded order payload normalization to accept nested list wrappers and object-shaped order responses.
- Expanded order detail field aliases for deadlines and objectives.
- Re-rendered cached dynamic feeds after changing locale.
- Improved loadout recommendation cards and added faction/playstyle-based armor guidance.
- Kept stratagem names as text because the supplied ZIP contains no official stratagem image assets; no fake official images were substituted.
