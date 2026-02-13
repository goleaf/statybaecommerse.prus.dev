## Why
Referral administration is split across multiple Filament navigation items, which fragments workflows and increases context switching for admins. The referral module also has placeholder CRUD schemas for some entities and lacks coherent seed data linking all referral tables with realistic Lithuanian content.

## What Changes
- Consolidate referral-related admin workflows under one visible Filament navigation entry: `Referrals`.
- Move referral sub-domains into tabbed management on the `Referrals` record page via relation managers:
  - Referral Campaigns
  - Referral Codes
  - Referral Code Statistics
  - Referral Code Usage Logs
  - Referral Rewards
  - Referral Reward Logs
  - Referral Statistics
- Keep auxiliary referral resources functional but hidden from left navigation.
- Complete missing CRUD schemas/tables for referral code statistics, usage logs, and referral statistics where currently stubbed.
- Add/update referral data seeding with Lithuanian-first texts and fully linked records across campaigns, codes, referrals, rewards, reward logs, usage logs, and statistics.
- Add regression tests for consolidated tabbed management and seed data integrity.

## Impact
- Affected specs: referrals-admin (new capability)
- Affected code:
  - `app/Filament/Resources/Referrals/*`
  - `app/Filament/Resources/Referral*/`
  - `app/Models/Referral.php`
  - `database/seeders/ReferralSystemComprehensiveSeeder.php`
  - `database/seeders/ReferralSystemSeeder.php`
  - referral-focused tests under `tests/Feature/*`
