# Loadout artwork fix v6

- Uses the exact stratagem icon file names hosted by the Helldivers Wiki instead of querying `pageimages` for generic page thumbnails.
- Loads each image from `Special:FilePath`, which redirects to the wiki's original file and avoids cross-origin API fetches.
- Tries alternate official file names for stratagems whose icons use weapon/backpack designations.
- Keeps the local icon fallback visible if all official file names fail; no broken-image icon or alt text is shown.
- Build marker: `LOADOUT-STRATAGEMS-WIKI-FILES-v6`.
