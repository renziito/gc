# DiverSignal v12 - Supreme Order objective display

- Corrected Arrowhead task `valueTypes` interpretation: type 3 is target count, type 11 is liberation-required, and type 12 is target planet.
- Prevented numeric counts/flags from being incorrectly resolved as planet IDs.
- Shows the actual target planet in liberation objective titles and summaries when the planet catalog is available.
- Removed misleading duplicate planet/faction rows caused by the incorrect value-type labels.
- Localized the new mission labels and liberation flag to Spanish.

Validation: PHP syntax and ZIP integrity checked. Live API/browser behavior was not tested.
