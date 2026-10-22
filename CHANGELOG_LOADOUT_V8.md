# Loadout images v8

- Replaced `wiki/Special:FilePath/...` with direct `https://helldivers.wiki.gg/images/<filename>` requests.
- Converts the candidate file names to Wiki.gg's direct underscore-separated `/images/` paths.
- Preloads the image before revealing the card image.
- Keeps the existing per-stratagem candidate names and fallback behavior.
- Build marker: `LOADOUT-STRATAGEMS-DIRECT-IMAGES-v8`.

The user's browser test demonstrated that a direct `/images/Eagle_Napalm_Airstrike_Stratagem_Icon.svg` URL opens, while `Special:FilePath` requests from the app were returning 403.
