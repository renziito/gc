# DiverSignal v11 - Supreme Order details and Spanish localization

- Localizes known Major Order titles, briefings, objective labels, and status strings when Spanish is selected.
- Re-renders cached order data when the language selector changes, so the Orders section updates without a reload.
- Adds a second line to each mission row with available target-planet information or a clear prompt to open details.
- Prefers localized labels based on task type instead of displaying generic English descriptions returned by the API.
- Keeps raw task values visible in the detail view when the source does not provide enough information to interpret them.
- Separates the order briefing from task instructions.

Note: planet names/progress are shown only when present in the synchronized planet catalog/API response.
