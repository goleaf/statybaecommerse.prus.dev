# Change: Ensure Supplier Sidebar Navigation Respects Permissions

## Why
Supplier CRUD routes and pages exist, but the sidebar item can be registered even when an admin user lacks `view_suppliers`. This creates a misleading navigation entry that leads to denied access.

## What Changes
- Gate `SupplierResource` sidebar registration with `canViewAny()`.
- Return no Supplier navigation items when the current user cannot view suppliers.
- Add regression tests for both authorized and unauthorized admin users.

## Impact
- Affected spec: `supplier-admin-navigation`
- No database or API changes.
- Admin sidebar behavior becomes permission-consistent for Supplier navigation.
