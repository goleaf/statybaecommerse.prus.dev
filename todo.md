# TODO (2025-11-03)

1. ✅ Diagnose `ProductFeatureSeeder` duplicate constraint failures on unique `(product_id, feature_type, feature_key)`.
2. ✅ Refactor `ProductFeatureSeeder` logic to ensure unique feature combinations per product while preserving randomized values.
3. ✅ Re-run targeted database seeding tests to confirm the unique constraint violation is resolved.
4. ✅ Audit `ProductVariantSeeder` attribute creation to prevent duplicate attribute slug collisions.
5. ✅ Refactor `ProductVariantSeeder` to reuse existing attributes and ensure attribute values remain unique per slug.
6. ✅ Re-run `ProductVariantSeeder` to verify seeding passes without unique constraint violations and update related tests.

