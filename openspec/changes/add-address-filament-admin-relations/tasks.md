## 1. Implementation
- [ ] 1.1 Add `AddressResource` with form, table, and pages following existing Filament v4 patterns
- [ ] 1.2 Add `UserResource` (or extend existing user admin resource if present) and include an `AddressesRelationManager`
- [ ] 1.3 Add `CountryResource` and `CityResource` relation managers that list related addresses
- [ ] 1.4 Ensure address relation managers avoid incorrect assumptions about missing foreign keys (e.g., customers)

## 2. Validation
- [ ] 2.1 Add/extend Filament feature tests that load `/admin/addresses` and relation managers without type errors
- [ ] 2.2 Run `composer run test` (or targeted Filament tests) and `composer run analyze` if feasible