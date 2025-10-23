# Feature Highlights

## System Setting Dependency Enhancements
- Admin users can now choose explicit comparison operators (equals, not equals, contains, etc.) and supply dedicated condition values when linking dependent settings.
- Operator-only rules such as "is empty" or "is true" automatically hide the value input, keeping forms clear while preventing invalid submissions.
- Backend evaluation normalizes numeric, string, list, and boolean comparisons to ensure dependencies trigger consistently across translations and seed data.
- Migration tooling converts legacy JSON-based conditions into the new operator/value columns so existing rules continue to work without manual edits.

## Localization & UX Touches
- English and Lithuanian admin translations now include readable labels for every supported dependency operator and the new condition value field.
