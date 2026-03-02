# Change: Add Account Address Editing

## Why
Customers can currently add, delete, and set default addresses on `/account/addresses`, but they cannot edit an existing saved address. This forces unnecessary delete/recreate workflows and increases user error risk.

## What Changes
- Add an edit action for each saved address in the account addresses list.
- Reuse the existing account address form for edit mode with prefilled address values.
- Persist changes to the selected address when the user saves.
- Keep existing ownership checks so users can only edit their own addresses.
- Preserve current default-address behavior and validation rules.

## Impact
- Affected specs: `account-addresses` (new capability)
- Affected code: `app/Livewire/Pages/Account/Addresses.php`, `resources/views/livewire/pages/account/addresses.blade.php`
