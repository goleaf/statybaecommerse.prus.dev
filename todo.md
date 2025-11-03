# TODO (2025-11-03)

1. ✅ Diagnose `ProductFeatureSeeder` duplicate constraint failures on unique `(product_id, feature_type, feature_key)`.
2. ✅ Refactor `ProductFeatureSeeder` logic to ensure unique feature combinations per product while preserving randomized values.
3. ✅ Re-run targeted database seeding tests to confirm the unique constraint violation is resolved.
4. ✅ Audit `ProductVariantSeeder` attribute creation to prevent duplicate attribute slug collisions.
5. ✅ Refactor `ProductVariantSeeder` to reuse existing attributes and ensure attribute values remain unique per slug.
6. ✅ Re-run `ProductVariantSeeder` to verify seeding passes without unique constraint violations and update related tests.

7. ✅ Investigate `ReferralSeeder` for duplicate `referred_id` values causing SQLite unique constraint failures.
8. ✅ Update `ReferralSeeder` factory usage to guarantee unique `referred_id` assignments per record.
9. ⚠️ Execute targeted referral seeder tests to confirm constraint stability on SQLite and MySQL connections (Filament resource test failures due to pre-existing missing imports).
10. ✅ Audit `ReferralSystemComprehensiveSeeder` for duplicate `referral_code` usage triggering SQLite unique constraint violations.
11. ✅ Expand referral code inventory and enforce one-to-one assignment between codes and referrals.
12. ✅ Re-run `ReferralSystemComprehensiveSeeder` to validate unique constraint compliance.
13. ✅ Audit `ReferralSystemSeeder` for duplicate `referral_code` usage triggering SQLite unique constraint violations.
14. ✅ Adjust `ReferralSystemSeeder` to guarantee unique referral codes per referral record.
15. ✅ Normalize `ReferralSystemSeeder` statistics generation to avoid `(user_id, date)` duplicates.
16. ✅ Re-run `ReferralSystemSeeder` to validate unique constraint compliance.
17. ✅ Audit `RolePermissionSeeder` for incorrect guard assignments on roles and permissions.
18. ✅ Normalize `RolePermissionSeeder` to enforce `admin` guard consistently.
19. ✅ Re-run `RolePermissionSeeder` to confirm the guard mismatch error is resolved.

