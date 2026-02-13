## ADDED Requirements

### Requirement: Single Referrals Navigation Entry
The admin panel SHALL expose referral management through one visible navigation item named `Referrals`.

#### Scenario: Auxiliary referral resources are hidden from left navigation
- **WHEN** an admin opens the Filament sidebar
- **THEN** only `Referrals` appears for referral administration
- **AND** auxiliary resources for campaigns, code statistics, usage logs, codes, reward logs, rewards, and statistics are not listed as separate menu items

### Requirement: Referrals Page Centralizes Referral Domain Management
The `Referrals` resource SHALL provide tabbed relation management for the referral ecosystem from one record page.

#### Scenario: Referral record shows all referral-domain tabs
- **WHEN** an admin opens a referral record edit page
- **THEN** relation tabs are available for:
  - Referral Campaigns
  - Referral Codes
  - Referral Code Statistics
  - Referral Code Usage Logs
  - Referral Rewards
  - Referral Reward Logs
  - Referral Statistics

#### Scenario: Each tab supports required CRUD where data is managed
- **WHEN** an admin uses a referral-domain tab
- **THEN** the tab supports create/edit/delete flows where the underlying table is mutable
- **AND** only operationally-needed CRUD is exposed for each tab

### Requirement: Lithuanian Referral Seed Data
Referral seeders SHALL generate Lithuanian-first content with linked records across existing referral tables.

#### Scenario: Seeded referral ecosystem is relationally consistent
- **WHEN** referral comprehensive seeding is executed
- **THEN** campaigns, codes, referrals, rewards, reward logs, usage logs, and statistics are created
- **AND** records are connected through existing foreign keys and business keys
- **AND** seed text fields include Lithuanian content for referral-domain copy
