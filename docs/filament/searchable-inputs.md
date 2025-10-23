# Searchable input hydration helper

`App\\Support\\Filament\\SearchableComponentHelper` centralises how Filament search widgets restore their state after Livewire
refreshes. Call the helper inside `afterStateHydrated()` or `afterStateUpdated()` hooks so every searchable input reuses the
same conventions.

## Hydrating from existing records

```php
use App\\Models\\User;
use App\\Support\\Filament\\SearchableComponentHelper;
use App\\Support\\Search\\SearchResultPayload;
use DefStudio\\SearchableInput\\DTO\\SearchResult;
use DefStudio\\SearchableInput\\Forms\\Components\\SearchableInput;

SearchableInput::make('product_id')
    ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
        SearchableComponentHelper::hydrate(
            component: $component,
            state: $state,
            resolveResult: function (int|string $identifier): ?SearchResult {
                $user = User::query()->with('profile')->find((int) $identifier);

                if (! $user instanceof User) {
                    return null;
                }

                return SearchResultPayload::normalise(
                    SearchResult::make(
                        (string) $user->getKey(),
                        sprintf('%s <%s>', $user->name, $user->email),
                    ),
                    [
                        'phone'   => $user->profile?->phone,
                        'company' => $user->profile?->company,
                    ],
                );
            },
        );
    });
```

1. **Resolver closure** – receives the persisted state and must return a `SearchResult` DTO (or `null` when nothing should hydrate).
2. **Canonical payload** – construct the DTO with `SearchResultPayload::normalise()` so the helper reuses the same `{ id, label, payload }` shape that Livewire emits.
3. **Automatic clearing** – when the resolver returns `null`, the helper resets the component state and options immediately, preventing stale metadata.

## Synchronising state after updates

```php
use App\\Models\\User;
use App\\Support\\Search\\SearchResultPayload;
use DefStudio\\SearchableInput\\DTO\\SearchResult;
use Filament\\Forms\\Set;

SearchableInput::make('user_lookup')
    ->afterStateUpdated(function (SearchableInput $component, ?string $state, Set $set): void {
        SearchableComponentHelper::sync(
            component: $component,
            state: $state,
            set: $set,
            targetField: 'user_id',
            resolveResult: static function (int|string $identifier): ?SearchResult {
                $user = User::query()->with('profile')->find((int) $identifier);

                if (! $user instanceof User) {
                    return null;
                }

                return SearchResultPayload::normalise(
                    SearchResult::make(
                        (string) $user->getKey(),
                        sprintf('%s <%s>', $user->name, $user->email),
                    ),
                    [
                        'phone'   => $user->profile?->phone,
                        'company' => $user->profile?->company,
                    ],
                );
            },
        );

        if ($state === null || $state === '') {
            $set('profile_payload', []);
        }
    });
```

- **Target field** – the helper writes the resolved identifier back to the provided `$targetField` using the Filament `Set` helper.
- **Type normalisation** – numeric identifiers are converted to integers automatically so Eloquent relationships receive the expected type.
- **Clearing dependants** – when the state is empty you can reset related fields immediately after `sync()` runs, as shown with the `profile_payload` field above.

## Clearing a component manually

Call `SearchableComponentHelper::clear($component);` whenever you need to wipe a lookup outside of the standard lifecycle hooks. The helper strips state and options so the user sees a blank input and no residual metadata.

## Quick checklist

- Keep the payload structure aligned with the search service that powers the component. For example, `AddressSearch::payload()` already exposes the exact fields expected by the order form, so return it directly from the resolver.
- When the component stores something other than the lookup identifier (for example, a composite key), make sure the `SearchResult` value mirrors the final persisted state; the helper pushes that value back into the component before rendering.
- If a lookup fails or the state is empty, the helper automatically calls `clear()` so the UI stays in sync with the database.

## Resource integration checklist

- Register `afterStateHydrated` closures on your Filament form components to call `SearchableComponentHelper::hydrate()` with a finder closure and normaliser that return the `[value, label, payload]` tuple described above. This keeps edit forms and relation managers aligned when records are re-opened.
- Pair `afterStateUpdated` hooks with `SearchableComponentHelper::clear()` so clearing the lookup also wipes any dependent state (`Set` helpers for foreign keys, cached payload fields, and related dropdowns).
- Prefer returning a payload array that is already shaped for the downstream Livewire data structure you need. The helper simply forwards the normalised payload, making the component the single source of truth for metadata.

## Related guidelines

- Review the broader [searchable input metadata lifecycle](../forms/SEARCHABLE_INPUT_METADATA.md) for payload conventions and integration examples.
- Keep Filament resource ergonomics consistent by following the [navigation structure guide](../filament-navigation-structure.md) and the [navigation group compatibility rule](../filament-v4-navigation-group-rule.md).
