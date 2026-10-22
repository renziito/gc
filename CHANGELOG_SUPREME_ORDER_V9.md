# Supreme Order rendering fix v9

- Reads the real Arrowhead assignment shape: `setting.overrideTitle`, `setting.overrideBrief`, `setting.taskDescription`, `setting.tasks`, `progress[]`, and `expiresIn`.
- Calculates aggregate progress from completed task flags in the progress array.
- Displays task status using the corresponding progress-array index.
- Maps common task types (11 liberate, 12 defend, 13 hold, 3 eradicate, 7 missions) to readable labels instead of showing `#11`.
- Supports the existing flattened data shape as a fallback.
