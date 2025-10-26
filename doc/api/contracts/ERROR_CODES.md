# API Error Codes & Recovery Contracts

This catalog centralizes every JSON error response emitted by the storefront and public API controllers. Each code entry links to the Laravel source and translation strings so integrators can trace behaviour quickly. Update this document whenever you introduce a new error response or modify an existing message.

## Domain Index
- [Location API](#location-api)
- [System Settings](#system-settings)
- [Notifications](#notifications)
- [Referral Reward APIs](#referral-rewards)
- [Storefront Utilities](#storefront-utilities)
- [Discount Codes](#discount-codes)
- [Campaign Analytics](#campaign-analytics)
- [Referral Program](#referral-program)
- [Navigation Menus](#navigation-menus)
- [News API](#news-api)
- [Inventory & Stock](#inventory-and-stock)
- [Autocomplete Service](#autocomplete-service)
- [Campaign Click API](#campaign-click-api)
- [Product History API](#product-history-api)
- [Attribute Tools](#attribute-tools)

---

## Location API {#location-api}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| LOC-400-001 | 400 | Latitude and longitude are required | – (falls back to English) | [Details](#loc-400-001) |

### LOC-400-001 — Missing coordinates {#loc-400-001}
- **Message source:** `response()->json(['error' => 'Latitude and longitude are required'], 400)` in the `nearby` handler.【F:app/Http/Controllers/LocationController.php†L90-L111】
- **Typical causes:** Omitted `latitude` or `longitude` query parameters when calling `/api/locations/nearby`.【F:app/Http/Controllers/LocationController.php†L98-L111】
- **Suggested recovery:** Provide both floating-point coordinates (`latitude`, `longitude`) and optionally `radius` (defaults to 10 km).【F:app/Http/Controllers/LocationController.php†L98-L111】

---

## System Settings {#system-settings}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| SYS-404-001 | 404 | Setting not found | Nustatymas nerastas | [Details](#sys-404-001) |

### SYS-404-001 — Unknown setting key {#sys-404-001}
- **Message sources:**
  - Public API: `response()->json(['error' => 'Setting not found'], 404)` when the requested key is missing or not public.【F:app/Http/Controllers/SystemSettingController.php†L130-L140】
  - Storefront JSON endpoint: `__('admin.system_settings.setting_not_found')` returned with a 404 when a public key cannot be located.【F:app/Http/Controllers/Frontend/SystemSettingsController.php†L122-L129】
- **Localized strings:** English and Lithuanian variants live in the enhanced settings bundle (`'not_found'`).【F:resources/lang/en/enhanced_settings.php†L84-L93】【F:resources/lang/lt/enhanced_settings.php†L84-L93】
- **Typical causes:** Requesting an inactive/non-public system setting, typo in the `key`, or stale cache after removing a setting.【F:app/Http/Controllers/SystemSettingController.php†L132-L140】
- **Suggested recovery:** Verify the `key` (case-sensitive), ensure the setting is active & public, then retry after cache refresh if recently modified.【F:app/Http/Controllers/SystemSettingController.php†L132-L140】

---

## Notifications {#notifications}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| NOTIF-404-001 | 404 | Notification not found | – (falls back to English) | [Details](#notif-404-001) |
| NOTIF-400-001 | 400 | Search query is required | – (falls back to English) | [Details](#notif-400-001) |

### NOTIF-404-001 — Notification missing {#notif-404-001}
- **Message sources:** JSON responses in both UI and API controllers emit `Notification not found` with HTTP 404 when an ID does not belong to the authenticated user.【F:app/Http/Controllers/NotificationController.php†L29-L76】【F:app/Http/Controllers/Api/NotificationController.php†L120-L127】
- **Typical causes:** Deleted notification IDs, attempting to read another user’s notification, or using stale identifiers after purge operations.【F:app/Http/Controllers/NotificationController.php†L29-L76】
- **Suggested recovery:** Refresh the user’s notification list, then retry with a valid ID; ensure the acting user owns the record.【F:app/Http/Controllers/Api/NotificationController.php†L123-L148】

### NOTIF-400-001 — Missing notification search term {#notif-400-001}
- **Message source:** `response()->json(['success' => false, 'message' => 'Search query is required'], 400)` when the `q` parameter is blank.【F:app/Http/Controllers/Api/NotificationController.php†L136-L145】
- **Typical causes:** Forgetting to supply `q` while filtering notifications via `/api/notifications/search`.
- **Suggested recovery:** Pass a non-empty search string (and optional `type`/`read` flags) to narrow results.【F:app/Http/Controllers/Api/NotificationController.php†L136-L148】

---

## Referral Reward APIs {#referral-rewards}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| RRW-401-001 | 401 | Unauthorized | – (falls back to English) | [Details](#rrw-401-001) |
| RRW-400-001 | 400 | Invalid type | – (falls back to English) | [Details](#rrw-400-001) |

### RRW-401-001 — Referral reward access denied {#rrw-401-001}
- **Message source:** Every `/referral-rewards/*` JSON endpoint checks `Auth::user()` and returns `['error' => 'Unauthorized']` with HTTP 401 when missing.【F:app/Http/Controllers/Frontend/ReferralRewardController.php†L53-L136】
- **Typical causes:** Calling reward APIs without an authenticated session or with expired tokens.
- **Suggested recovery:** Authenticate the customer first (web session or API token) before hitting reward endpoints.【F:app/Http/Controllers/Frontend/ReferralRewardController.php†L53-L136】

### RRW-400-001 — Unsupported reward type {#rrw-400-001}
- **Message source:** `apiByType` guards against unknown `type` values and returns `['error' => 'Invalid type']` with HTTP 400.【F:app/Http/Controllers/Frontend/ReferralRewardController.php†L109-L126】
- **Typical causes:** Passing a type outside `referrer_bonus` or `referred_discount`.
- **Suggested recovery:** Restrict the `type` query parameter to the supported enum set when filtering rewards.【F:app/Http/Controllers/Frontend/ReferralRewardController.php†L109-L126】

---

## Storefront Utilities {#storefront-utilities}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| STO-401-001 | 401 | Unauthorized | – (falls back to English) | [Details](#sto-401-001) |

### STO-401-001 — Wishlist toggle requires login {#sto-401-001}
- **Message source:** The storefront API rejects wishlist mutations without a signed-in user via `['error' => 'Unauthorized']` and HTTP 401.【F:app/Http/Controllers/Frontend/ApiController.php†L63-L87】
- **Typical causes:** Attempting to add/remove wishlist items while browsing anonymously.
- **Suggested recovery:** Prompt the shopper to sign in (or attach an API token) before toggling wishlist entries.【F:app/Http/Controllers/Frontend/ApiController.php†L63-L87】

---

## Discount Codes {#discount-codes}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| DISC-422-001 | 422 | Invalid Code | Netinkamas kodas | [Details](#disc-422-001) |
| DISC-422-002 | 422 | Code already used | Kodas jau panaudotas | [Details](#disc-422-002) |
| DISC-500-001 | 500 | Something went wrong. Please try again. | Kažkas nutiko. Prašome bandyti dar kartą. | [Details](#disc-500-001) |
| DISC-500-002 | 500 | Failed to generate document | Nepavyko sugeneruoti dokumento | [Details](#disc-500-002) |

### DISC-422-001 — Discount code invalid {#disc-422-001}
- **Localized strings:** English & Lithuanian JSON translations provide the customer-facing copy.【F:lang/en.json†L450-L458】【F:lang/lt.json†L470-L486】
- **Message sources:** Validation endpoints reject missing/expired/inactive codes via `__('discount_code_invalid')` (HTTP 422).【F:app/Http/Controllers/Frontend/DiscountCodeController.php†L33-L69】【F:app/Http/Controllers/Frontend/DiscountCodeController.php†L99-L114】
- **Typical causes:** Unknown code, expired discount, inactive promotion, or limit exceeded without specific override.【F:app/Http/Controllers/Frontend/DiscountCodeController.php†L33-L69】
- **Suggested recovery:** Present a new/active code; ensure campaign limits and activation dates are satisfied before re-submitting.【F:app/Http/Controllers/Frontend/DiscountCodeController.php†L33-L69】

### DISC-422-002 — Discount code already used {#disc-422-002}
- **Localized strings:** Stored alongside other discount copy in the JSON dictionaries.【F:lang/en.json†L453-L461】【F:lang/lt.json†L480-L488】
- **Message source:** During validation the controller checks per-user usage limits and emits `__('discount_code_already_used')` when exceeded.【F:app/Http/Controllers/Frontend/DiscountCodeController.php†L49-L55】
- **Typical causes:** The authenticated customer exhausted their per-user redemption allowance for the code.
- **Suggested recovery:** Offer alternative incentives or increase `usage_limit_per_user` if business rules allow.【F:app/Http/Controllers/Frontend/DiscountCodeController.php†L49-L55】

### DISC-500-001 — Generic discount failure {#disc-500-001}
- **Localized strings:** Shared JSON translation ensures both locales show the same fallback text.【F:lang/en.json†L457-L520】【F:lang/lt.json†L483-L543】
- **Message source:** `apply()` catches runtime exceptions and responds with `__('Something went wrong. Please try again.')` (HTTP 500).【F:app/Http/Controllers/Frontend/DiscountCodeController.php†L63-L92】
- **Typical causes:** Unexpected database failures while recording redemptions, race conditions while incrementing usage, or transient document service outages.
- **Suggested recovery:** Retry after confirming code status, inspect server logs for the captured exception, and ensure idempotent redemption handling.【F:app/Http/Controllers/Frontend/DiscountCodeController.php†L63-L92】

### DISC-500-002 — Discount document generation failure {#disc-500-002}
- **Localized strings:** Translation key `admin.notifications.document_generation_failed` supplies both EN/LT labels.【F:lang/en.json†L482-L488】【F:lang/lt.json†L503-L515】
- **Message source:** The export endpoint wraps the document service call and returns `['error' => 'Failed to generate document']` on exceptions (HTTP 500).【F:app/Http/Controllers/Frontend/DiscountCodeController.php†L150-L158】
- **Typical causes:** Rendering engine errors, missing templates, or upstream storage failures while building PDFs/HTML.
- **Suggested recovery:** Verify template availability (`template_id`), confirm export format support, and inspect document service logs before retrying.【F:app/Http/Controllers/Frontend/DiscountCodeController.php†L150-L158】

---

## Campaign Analytics {#campaign-analytics}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| CAMP-400-001 | 400 | No campaigns selected for comparison | Nepasirinkta kampanijų palyginimui | [Details](#camp-400-001) |

### CAMP-400-001 — Missing comparison set {#camp-400-001}
- **Localized strings:** Campaign translation bundles carry the EN/LT wording.【F:lang/en/translations.php†L455-L458】【F:lang/lt/translations.php†L452-L459】
- **Message source:** `/campaigns/comparison` returns HTTP 400 when `campaign_ids` is empty.【F:app/Http/Controllers/Frontend/CampaignController.php†L186-L198】
- **Typical causes:** Client forgets to submit at least one campaign ID.
- **Suggested recovery:** Provide an array of campaign IDs to compare before resending the request.【F:app/Http/Controllers/Frontend/CampaignController.php†L186-L198】

---

## Referral Program {#referral-program}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| REF-409-001 | 200* | You already have an active referral code | Jau turite aktyvų rekomendacijos kodą | [Details](#ref-409-001) |
| REF-500-001 | 200* | Failed to generate referral code. Please try again. | Nepavyko sugeneruoti rekomendacijos kodo. Bandykite dar kartą. | [Details](#ref-500-001) |
| REF-422-001 | 200* | Validation message from Laravel validator | Lokalizuojama pagal validacijos taisykles | [Details](#ref-422-001) |
| REF-409-002 | 200* | This user has already been referred | Šis vartotojas jau rekomenduotas | [Details](#ref-409-002) |
| REF-404-001 | 200* | Invalid referral code | Neteisingas rekomendacijos kodas | [Details](#ref-404-001) |
| REF-403-001 | 200* | You cannot use your own referral code | Negalite naudoti savo rekomendacijos kodo | [Details](#ref-403-001) |
| REF-500-002 | 200* | Failed to apply referral code. Please try again. | Nepavyko pritaikyti rekomendacijos kodo. Bandykite dar kartą. | [Details](#ref-500-002) |

> **Note:** Referral JSON endpoints use HTTP 200 with `success: false` for domain errors (marked with `200*`).

### REF-409-001 — Duplicate active referral code {#ref-409-001}
- **Localized strings:** Defined in the referral locale bundles.【F:lang/en/referrals.php†L300-L305】【F:lang/lt/referrals.php†L200-L208】
- **Message source:** `generateCode()` prevents issuing multiple active codes per user.【F:app/Http/Controllers/Frontend/ReferralController.php†L104-L118】
- **Typical causes:** Customer already generated a referral code and requests another without deactivating the first.
- **Suggested recovery:** Inform the user of their existing code (returned in the response) and allow them to reuse or revoke it.【F:app/Http/Controllers/Frontend/ReferralController.php†L104-L118】

### REF-500-001 — Referral code generation failure {#ref-500-001}
- **Localized strings:** See referral bundles for EN/LT messaging.【F:lang/en/referrals.php†L272-L276】【F:lang/lt/referrals.php†L291-L295】
- **Message source:** Exception catch around unique code generation returns the failure notice.【F:app/Http/Controllers/Frontend/ReferralController.php†L112-L118】
- **Typical causes:** Database uniqueness conflicts or random generator errors.
- **Suggested recovery:** Retry after a short delay; monitor logs for collisions if the pool is saturated.【F:app/Http/Controllers/Frontend/ReferralController.php†L112-L118】

### REF-422-001 — Referral validation errors {#ref-422-001}
- **Message source:** When the `code` parameter fails validation, Laravel’s validator returns the first localized validation message.【F:app/Http/Controllers/Frontend/ReferralController.php†L124-L129】
- **Typical causes:** Missing code field, exceeding max length, or invalid characters.
- **Suggested recovery:** Ensure the referral code input meets the `required|string|max:20` constraints before resubmitting.【F:app/Http/Controllers/Frontend/ReferralController.php†L124-L129】

### REF-409-002 — User already referred {#ref-409-002}
- **Localized strings:** Provided for both locales.【F:lang/en/referrals.php†L300-L304】【F:lang/lt/referrals.php†L200-L204】
- **Message source:** The controller checks referral history and blocks duplicates.【F:app/Http/Controllers/Frontend/ReferralController.php†L130-L134】
- **Typical causes:** Attempting to apply a referral after already benefiting from another code.
- **Suggested recovery:** Inform the user they cannot redeem multiple referral invitations.【F:app/Http/Controllers/Frontend/ReferralController.php†L130-L134】

### REF-404-001 — Referral code not found or inactive {#ref-404-001}
- **Localized strings:** Shared translations cover both “not found” and “invalid” cases.【F:lang/en/referrals.php†L300-L309】【F:lang/lt/referrals.php†L200-L208】
- **Message source:** Returned when `Referral::findByCode` fails or the record is invalid.【F:app/Http/Controllers/Frontend/ReferralController.php†L135-L143】
- **Typical causes:** Typo, expired code, or deactivated referral.
- **Suggested recovery:** Ask the referrer for a fresh code or generate a new one if eligible.【F:app/Http/Controllers/Frontend/ReferralController.php†L135-L143】

### REF-403-001 — Referrer attempting self-use {#ref-403-001}
- **Localized strings:** Prevents users from consuming their own codes.【F:lang/en/referrals.php†L275-L276】【F:lang/lt/referrals.php†L293-L295】
- **Message source:** Compares the referrer and authenticated user IDs before allowing application.【F:app/Http/Controllers/Frontend/ReferralController.php†L144-L147】
- **Typical causes:** Users entering their own referral code.
- **Suggested recovery:** Prompt the user to share the code with others instead of self-redeeming.【F:app/Http/Controllers/Frontend/ReferralController.php†L144-L147】

### REF-500-002 — Referral apply failure {#ref-500-002}
- **Localized strings:** Error copy stored in locale bundles.【F:lang/en/referrals.php†L272-L276】【F:lang/lt/referrals.php†L291-L295】
- **Message source:** Database updates during referral application run inside a transaction; any exception triggers this response.【F:app/Http/Controllers/Frontend/ReferralController.php†L148-L158】
- **Typical causes:** Transaction conflicts or downstream persistence issues.
- **Suggested recovery:** Retry after inspecting logs; ensure related referral records exist and are writable.【F:app/Http/Controllers/Frontend/ReferralController.php†L148-L158】

---

## Navigation Menus {#navigation-menus}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| MENU-404-001 | 404 | api.menu_not_found (translation missing) | – | [Details](#menu-404-001) |
| MENU-404-002 | 404 | api.menu_not_found_for_location (translation missing) | – | [Details](#menu-404-002) |

### MENU-404-001 — Menu key not found {#menu-404-001}
- **Message source:** The menu controller returns `__('api.menu_not_found')` with HTTP 404 when a keyed menu cannot be loaded.【F:app/Http/Controllers/Frontend/MenuController.php†L46-L56】
- **Localization note:** The `api.menu_not_found` string is currently undefined; Laravel will echo the key in all locales. Add translations when available.【F:app/Http/Controllers/Frontend/MenuController.php†L46-L56】
- **Typical causes:** Requesting a disabled menu or using an incorrect key.
- **Suggested recovery:** Confirm the menu exists, is active, and the correct slug/key is used before retrying.【F:app/Http/Controllers/Frontend/MenuController.php†L46-L56】

### MENU-404-002 — Menu not available for location {#menu-404-002}
- **Message source:** `byLocation` returns `__('api.menu_not_found_for_location')` for absent menus at a given location.【F:app/Http/Controllers/Frontend/MenuController.php†L60-L70】
- **Localization note:** Translation key currently lacks string resources; add locale entries to avoid exposing the raw key.【F:app/Http/Controllers/Frontend/MenuController.php†L60-L70】
- **Typical causes:** No active menu assigned to the supplied location identifier.
- **Suggested recovery:** Assign a menu to that location or fall back to a default key before requesting JSON data.【F:app/Http/Controllers/Frontend/MenuController.php†L60-L70】

---

## News API {#news-api}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| NEWS-404-001 | 404 | api.news_not_found (translation missing) | – | [Details](#news-404-001) |

### NEWS-404-001 — News article not found {#news-404-001}
- **Message source:** When an article slug is missing, the controller responds with `__('api.news_not_found')` and HTTP 404.【F:app/Http/Controllers/Frontend/NewsController.php†L67-L87】
- **Localization note:** Provide translations for `api.news_not_found` to avoid leaking the key to clients.【F:app/Http/Controllers/Frontend/NewsController.php†L67-L87】
- **Typical causes:** Invalid slug, unpublished content, or deleted article.
- **Suggested recovery:** Refresh article listings to obtain valid slugs or publish the content before exposing the endpoint.【F:app/Http/Controllers/Frontend/NewsController.php†L67-L87】

---

## Inventory & Stock {#inventory-and-stock}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| INV-500-001 | 500 | inventory.adjustment_failed (translation missing) | – | [Details](#inv-500-001) |
| INV-400-001 | 400 | inventory.reserve_failed_message (translation missing) | – | [Details](#inv-400-001) |
| INV-500-002 | 500 | inventory.reserve_failed (translation missing) | – | [Details](#inv-500-002) |
| INV-500-003 | 500 | inventory.unreserve_failed (translation missing) | – | [Details](#inv-500-003) |

> **Localization note:** Failure strings for inventory actions currently lack locale entries. Add them under `lang/*/inventory.php` to present user-friendly messages.

### INV-500-001 — Stock adjustment failure {#inv-500-001}
- **Message source:** Catch block in `adjustStock` returns `__('inventory.adjustment_failed')` with the captured exception (HTTP 500).【F:app/Http/Controllers/StockController.php†L80-L91】
- **Typical causes:** Business rule exceptions thrown by `VariantInventory::adjustStock` (e.g., invalid adjustment reasons or concurrency conflicts).
- **Suggested recovery:** Confirm request payload and inspect the accompanying `error` field for root cause details before retrying.【F:app/Http/Controllers/StockController.php†L80-L91】

### INV-400-001 — Reserve request rejected {#inv-400-001}
- **Message source:** When `reserve()` returns `false`, the controller emits `__('inventory.reserve_failed_message')` with HTTP 400.【F:app/Http/Controllers/StockController.php†L97-L106】
- **Typical causes:** Insufficient available stock relative to requested quantity.
- **Suggested recovery:** Lower the reservation quantity or restock inventory before repeating the call.【F:app/Http/Controllers/StockController.php†L97-L108】

### INV-500-002 — Reserve operation crashed {#inv-500-002}
- **Message source:** Exceptions within `reserve()` return `__('inventory.reserve_failed')` and the thrown message (HTTP 500).【F:app/Http/Controllers/StockController.php†L97-L109】
- **Typical causes:** Data integrity issues while updating reservations or downstream persistence failures.
- **Suggested recovery:** Inspect the attached `error` field, resolve the underlying exception, then retry once the inventory record is consistent.【F:app/Http/Controllers/StockController.php†L97-L109】

### INV-500-003 — Unreserve operation crashed {#inv-500-003}
- **Message source:** `unreserveStock` emits `__('inventory.unreserve_failed')` when exceptions arise (HTTP 500).【F:app/Http/Controllers/StockController.php†L113-L125】
- **Typical causes:** Attempting to unreserve more units than currently reserved or transaction rollbacks.
- **Suggested recovery:** Validate the reserved quantity prior to the call and reconcile reservation logs if totals drift.【F:app/Http/Controllers/StockController.php†L113-L125】

---

## Autocomplete Service {#autocomplete-service}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| AUTO-422-001 | 422 | Validation failed | – (falls back to English) | [Details](#auto-422-001) |
| AUTO-401-001 | 401 | Authentication required | – (falls back to English) | [Details](#auto-401-001) |
| AUTO-500-001 | 500 | Search failed | – (falls back to English) | [Details](#auto-500-aux) |
| AUTO-500-002 | 500 | Product search failed | – | [Details](#auto-500-aux) |
| AUTO-500-003 | 500 | Category search failed | – | [Details](#auto-500-aux) |
| AUTO-500-004 | 500 | Brand search failed | – | [Details](#auto-500-aux) |
| AUTO-500-005 | 500 | Collection search failed | – | [Details](#auto-500-aux) |
| AUTO-500-006 | 500 | Attribute search failed | – | [Details](#auto-500-aux) |
| AUTO-500-007 | 500 | Failed to get popular suggestions | – | [Details](#auto-500-aux) |
| AUTO-500-008 | 500 | Failed to get recent suggestions | – | [Details](#auto-500-aux) |
| AUTO-500-009 | 500 | Failed to clear recent searches | – | [Details](#auto-500-aux) |
| AUTO-500-010 | 500 | Failed to get suggestions | – | [Details](#auto-500-aux) |
| AUTO-500-011 | 500 | Fuzzy search failed | – | [Details](#auto-500-aux) |
| AUTO-500-012 | 500 | Failed to get personalized suggestions | – | [Details](#auto-500-aux) |
| AUTO-500-013 | 500 | Customer search failed | – | [Details](#auto-500-aux) |
| AUTO-500-014 | 500 | Address search failed | – | [Details](#auto-500-aux) |
| AUTO-500-015 | 500 | Location search failed | – | [Details](#auto-500-aux) |
| AUTO-500-016 | 500 | Country search failed | – | [Details](#auto-500-aux) |
| AUTO-500-017 | 500 | City search failed | – | [Details](#auto-500-aux) |
| AUTO-500-018 | 500 | Order search failed | – | [Details](#auto-500-aux) |
| AUTO-500-019 | 500 | Paginated search failed | – | [Details](#auto-500-aux) |
| AUTO-500-020 | 500 | Export failed | – | [Details](#auto-500-aux) |
| AUTO-404-001 | 404 | Export not found or expired | – | [Details](#auto-404-001) |
| AUTO-500-021 | 500 | Download failed | – | [Details](#auto-500-aux) |
| AUTO-500-022 | 500 | Share failed | – | [Details](#auto-500-aux) |
| AUTO-404-002 | 404 | Shared search not found or expired | – | [Details](#auto-404-002) |
| AUTO-500-023 | 500 | View shared search failed | – | [Details](#auto-500-aux) |
| AUTO-500-024 | 500 | Get filters failed | – | [Details](#auto-500-aux) |
| AUTO-500-025 | 500 | Get insights failed | – | [Details](#auto-500-aux) |
| AUTO-500-026 | 500 | Get recommendations failed | – | [Details](#auto-500-aux) |
| AUTO-500-027 | 500 | Get analytics failed | – | [Details](#auto-500-aux) |

### AUTO-422-001 — Invalid autocomplete request {#auto-422-001}
- **Message source:** Every autocomplete endpoint wraps validation errors with `['success' => false, 'message' => 'Validation failed', 'errors' => …]` and returns HTTP 422.【F:app/Http/Controllers/Api/AutocompleteController.php†L28-L98】【F:app/Http/Controllers/Api/AutocompleteController.php†L105-L365】
- **Typical causes:** Search queries shorter than two characters, invalid limit bounds, or unsupported `types` filters.【F:app/Http/Controllers/Api/AutocompleteController.php†L30-L118】
- **Suggested recovery:** Conform to the documented validation rules (minimum length, maximum limit, allowed enumerations) before retrying.【F:app/Http/Controllers/Api/AutocompleteController.php†L30-L118】

### AUTO-401-001 — Personalized suggestions require auth {#auto-401-001}
- **Message source:** The `personalized` endpoint enforces authentication via `['success' => false, 'message' => 'Authentication required']` (HTTP 401).【F:app/Http/Controllers/Api/AutocompleteController.php†L233-L254】
- **Typical causes:** Anonymous users requesting personalized recommendations.
- **Suggested recovery:** Log the user in and include their session/cookie before fetching personalized data.【F:app/Http/Controllers/Api/AutocompleteController.php†L233-L254】

### AUTO-500-XXX — Autocomplete service failures {#auto-500-aux}
- **Message sources:** Each specialized handler wraps unexpected exceptions with a contextual message (`'Search failed'`, `'Product search failed'`, …) and returns HTTP 500 along with the exception payload in `error`.【F:app/Http/Controllers/Api/AutocompleteController.php†L28-L675】
- **Typical causes:** Downstream service outages (Elasticsearch/catalog queries), timeouts, or serialization problems within `AutocompleteService`.
- **Suggested recovery:** Review the `error` field for precise stack traces, retry after ensuring backing services (catalog, search indices, export storage) are operational, and guard client retries with exponential backoff.【F:app/Http/Controllers/Api/AutocompleteController.php†L28-L675】

### AUTO-404-001 — Export not found {#auto-404-001}
- **Message source:** Downloading exports with expired tokens triggers `['success' => false, 'message' => 'Export not found or expired']` (HTTP 404).【F:app/Http/Controllers/Api/AutocompleteController.php†L438-L469】
- **Suggested recovery:** Re-trigger export generation and use the fresh token before expiry.【F:app/Http/Controllers/Api/AutocompleteController.php†L438-L469】

### AUTO-404-002 — Shared search token invalid {#auto-404-002}
- **Message source:** Viewing shared searches validates the token and emits `['success' => false, 'message' => 'Shared search not found or expired']` (HTTP 404) when missing.【F:app/Http/Controllers/Api/AutocompleteController.php†L499-L523】
- **Suggested recovery:** Regenerate the share link or verify the token has not expired/been revoked before consuming it.【F:app/Http/Controllers/Api/AutocompleteController.php†L499-L523】

---

## Campaign Click API {#campaign-click-api}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| CCLK-403-001 | 403 | You are not authorized to perform this action | Neturite teisės atlikti šį veiksmą | [Details](#cclk-403-001) |
| CCLK-400-001 | 400 | Unsupported export format | Nepalaikomas eksporto formatas | [Details](#cclk-400-001) |

### CCLK-403-001 — Campaign click permission denied {#cclk-403-001}
- **Localized strings:** Available in both English and Lithuanian resource bundles.【F:resources/lang/en.php†L425-L433】【F:resources/lang/lt.php†L419-L427】
- **Message source:** The API returns HTTP 403 for unauthorized manipulation attempts (`CampaignClickController`).【F:app/Http/Controllers/Api/CampaignClickController.php†L83-L110】
- **Typical causes:** Authenticated user lacks the required ability/role to manage campaign clicks.
- **Suggested recovery:** Ensure the caller has sufficient privileges (admin scope) before retrying the operation.【F:app/Http/Controllers/Api/CampaignClickController.php†L83-L110】

### CCLK-400-001 — Unsupported campaign export format {#cclk-400-001}
- **Localized strings:** Export error strings stored with the campaign click translations.【F:resources/lang/en.php†L425-L433】【F:resources/lang/lt.php†L419-L427】
- **Message source:** Export endpoint validates requested formats and returns HTTP 400 for unsupported types.【F:app/Http/Controllers/Api/CampaignClickController.php†L195-L195】
- **Suggested recovery:** Limit the `format` parameter to the supported values advertised by the API (e.g., CSV, XLSX).【F:app/Http/Controllers/Api/CampaignClickController.php†L195-L195】

---

## Product History API {#product-history-api}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| HIST-404-001 | 404 | History not found for this product | – (falls back to English) | [Details](#hist-404-001) |

### HIST-404-001 — History entry does not belong to product {#hist-404-001}
- **Message source:** The show endpoint enforces that the history record matches the provided product, otherwise returning HTTP 404.【F:app/Http/Controllers/Api/ProductHistoryController.php†L64-L72】
- **Typical causes:** Requesting a history ID for a different product or after the record was removed.
- **Suggested recovery:** Fetch the product’s history collection first to obtain valid IDs, then retry with a matching resource identifier.【F:app/Http/Controllers/Api/ProductHistoryController.php†L40-L72】

---

## Attribute Tools {#attribute-tools}

| Identifier | HTTP | Message (EN) | Message (LT) | Anchor |
| --- | --- | --- | --- | --- |
| ATTR-400-001 | 200* | Attribute ID is required | – (falls back to English) | [Details](#attr-400-001) |
| ATTR-404-001 | 200* | Attribute not found | – | [Details](#attr-404-001) |
| ATTR-400-002 | 200* | At least 2 attribute IDs are required | – | [Details](#attr-400-002) |

> **Note:** Attribute helpers return HTTP 200 with `error` payloads (marked `200*`).

### ATTR-400-001 — Missing attribute identifier {#attr-400-001}
- **Message source:** `getAttributeStatistics` enforces the `attribute_id` parameter and emits the error when missing.【F:app/Http/Controllers/Frontend/AttributeController.php†L137-L149】
- **Typical causes:** Client omitted the `attribute_id` query parameter.
- **Suggested recovery:** Supply a valid attribute ID (as returned by attribute listings) before retrying.【F:app/Http/Controllers/Frontend/AttributeController.php†L137-L149】

### ATTR-404-001 — Attribute not found {#attr-404-001}
- **Message source:** The same endpoint returns `['error' => 'Attribute not found']` when the ID cannot be resolved.【F:app/Http/Controllers/Frontend/AttributeController.php†L137-L149】
- **Typical causes:** Deleted attributes or IDs outside the authenticated user’s scope.
- **Suggested recovery:** Refresh the attribute catalog and re-request statistics using an active ID.【F:app/Http/Controllers/Frontend/AttributeController.php†L137-L149】

### ATTR-400-002 — Insufficient attribute IDs for comparison {#attr-400-002}
- **Message source:** `getAttributeComparison` requires at least two IDs and returns `['error' => 'At least 2 attribute IDs are required']` otherwise.【F:app/Http/Controllers/Frontend/AttributeController.php†L227-L239】
- **Typical causes:** Passing fewer than two IDs to the comparison endpoint.
- **Suggested recovery:** Submit an array of two or more attribute IDs to receive comparison data.【F:app/Http/Controllers/Frontend/AttributeController.php†L227-L239】

---

## Product & Location Messaging Alignment

Ensure new controllers follow the same pattern: provide localized messages, include both English and Lithuanian translations, and update this catalog with an anchor link for quick reference. Review missing translations (`api.*`, `inventory.*`) and add locale files so API consumers do not see raw translation keys.

