# Searchable input hydration helper

The `App\\Support\\Filament\\Components\\SearchableComponentHelper` centralises the repetitive wiring required to keep [DefStudio's `SearchableInput`](https://github.com/defstudio/filament-searchable-input) fields hydrated with the correct state, options, and payload metadata inside our Filament forms. Use it whenever a Filament form component needs to look up a record, expose a human-readable label, and share structured payload data with sibling inputs.

## Hydrating from existing records

```php
use App\Support\Filament\Components\SearchableComponentHelper;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Components\Hidden;

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

Hidden::make('user_lookup_payload')
    ->default([])
    ->dehydrated(false); // Cache the lookup metadata for sibling components without persisting it.
```

1. **Resolver closure** – receives the persisted state and must return a `SearchResult` DTO (or `null` when nothing should hydrate).
2. **Canonical payload** – construct the DTO with `SearchResultPayload::normalise()` so the helper reuses the same `{ id, label, payload }` shape that Livewire emits.
3. **Automatic clearing** – when the resolver returns `null`, the helper resets the component state and options immediately, preventing stale metadata.

The helper converts the `value` to a string, registers it as the component state, and feeds the label through `options()` alongside the payload so downstream closures all read the same structure. When the normaliser returns an `Arrayable` payload (for example, a DTO implementing `toArray()`), the helper coerces it into an array before handing it over to Livewire. Empty or falsy identifiers short-circuit into `clear()` so the UI cannot surface stale metadata.

## Clearing a component

When a lookup is wiped out (for example, in an `afterStateUpdated` hook that receives a blank value), call the `clear()` helper to reset the state, options, and payload. Optional callbacks let you synchronise related form fields at the same time. `syncSelectedRecord()` already delegates to `clear()` when the lookup fails or the state is empty, but the standalone helper remains handy for bespoke flows (such as cascading dropdowns that need to wipe multiple fields).

```php
use App\Support\Filament\Components\SearchableComponentHelper;
use Filament\Forms\Get;
use Filament\Forms\Set;

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

Hidden payload fields (paired with `->dehydrated(false)`) keep the normalised metadata available to downstream components while ensuring `clear()` can wipe both the identifier and its cached payload in tandem.

## Normalisation tips

Call `SearchableComponentHelper::clear($component);` whenever you need to wipe a lookup outside of the standard lifecycle hooks. The helper strips state and options so the user sees a blank input and no residual metadata.

## Quick checklist

- Keep the payload structure aligned with the search service that powers the component. For example, `AddressSearch::payload()` already exposes the exact fields expected by the order form, so return it directly from the resolver.
- When the component stores something other than the lookup identifier (for example, a composite key), make sure the `SearchResult` value mirrors the final persisted state; the helper pushes that value back into the component before rendering.
- If a lookup fails or the state is empty, the helper automatically calls `clear()` so the UI stays in sync with the database.

## Resource integration checklist

- Register `afterStateHydrated` closures on your Filament form components to call `SearchableComponentHelper::hydrate()` with a finder closure and normaliser that return the `[value, label, payload]` tuple described above. This keeps edit forms and relation managers aligned when records are re-opened.
- Pair `afterStateUpdated` hooks with `SearchableComponentHelper::syncSelectedRecord()` so related attributes update automatically and dependent payloads can be cached or cleared through the optional callbacks.
- Prefer returning a payload array that is already shaped for the downstream Livewire data structure you need. The helper simply forwards the normalised payload, making the component the single source of truth for metadata.
- Keep resource `form()` signatures aligned with Filament's documented `public static function form(Form $form): Form` contract. Static analysis and Composer's package discovery checks rely on this signature, so mismatches can halt installs before searchable components boot.

## Related guidelines

- Review the broader [searchable input metadata lifecycle](../forms/SEARCHABLE_INPUT_METADATA.md) for payload conventions and integration examples.
- Keep Filament resource ergonomics consistent by following the [navigation structure guide](../filament-navigation-structure.md) and the [navigation group compatibility rule](../filament-v4-navigation-group-rule.md).
