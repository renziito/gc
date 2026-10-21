# Change log — corrective patch

- Preserved the supplied Galactic Command / DiverSignal project and its existing tactical UI.
- Added an accessible order-details dialog for each synchronized major order, exposing briefing, task progress, deadline, and reward data when present.
- Major-order briefing text now uses the same safe BBCode-aware renderer as dispatches and Steam news.
- Archive entries retain BBCode formatting rather than stripping it into plain text.
- Added safe handling for BBCode URL links and malformed `<i=3>`-style tags.
- Refined archive rich-text styles and loadout card styling without replacing the existing design.
- Removed bundled GitHub workflow and runtime logs from the deployment archive.
- Validation: all PHP files pass `php -l`; inline JavaScript and modules.js pass `node --check`.

## Remaining integration note
The supplied project does not include official individual stratagem artwork files. This patch preserves the existing loadout visuals and improves the presentation; official artwork still needs to be added to `public/assets/stratagems/` from authorized/source-verified game assets before each card can display the exact in-game image.
