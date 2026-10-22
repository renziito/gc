# Loadout artwork fix v6

- Uses the exact stratagem icon file names hosted by the Helldivers Wiki instead of querying `pageimages` for generic page thumbnails.
- Loads each image from `Special:FilePath`, which redirects to the wiki's original file and avoids cross-origin API fetches.
- Tries alternate official file names for stratagems whose icons use weapon/backpack designations.
- Keeps the local icon fallback visible if all official file names fail; no broken-image icon or alt text is shown.
- Build marker: `LOADOUT-STRATAGEMS-WIKI-FILES-v6`.

\n## Loadout artwork fix v7

- Fixed hidden + lazy image loading deadlock: artwork is preloaded through a separate `Image()` object.
- Only unhides the visible card image after the preload succeeds and `naturalWidth` is greater than zero.
- Preserves the exact Wiki.gg file candidates and fallback behavior.
- Build marker: `LOADOUT-STRATAGEMS-WIKI-FILES-v7`.
