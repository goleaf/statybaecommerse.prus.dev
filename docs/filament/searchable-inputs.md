# Searchable input helper usage

The `App\\Support\\Filament\\SearchableComponentHelper` centralises the repetitive wiring required to keep [DefStudio's `SearchableInput`](https://github.com/defstudio/filament-searchable-input) fields hydrated with the correct state, options, and payload metadata inside our Filament forms. Use it whenever a Filament form component needs to look up a record, expose a human-readable label, and share structured payload data with sibling inputs.

## Hydrating a component

```php
use App\\Models\\User;
use App\\Support\\Filament\\SearchableComponentHelper;
use App\\Support\\Search\\SearchResultPayload;
use DefStudio\\SearchableInput\\DTO\\SearchResult;
use DefStudio\\SearchableInput\\Forms\\Components\\SearchableInput;

SearchableInput::make('user_lookup')
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

## Normalisation tips

- Keep the payload structure aligned with the search service that powers the component. For example, `AddressSearch::payload()` already exposes the exact fields expected by the order form, so return it directly from the resolver.
- When the component stores something other than the lookup identifier (for example, a composite key), make sure the `SearchResult` value mirrors the final persisted state; the helper pushes that value back into the component before rendering.
- If a lookup fails or the state is empty, the helper automatically calls `clear()` so the UI stays in sync with the database.

## Related guidelines

- Review the broader [searchable input metadata lifecycle](../forms/SEARCHABLE_INPUT_METADATA.md) for payload conventions and integration examples.
- Keep Filament resource ergonomics consistent by following the [navigation structure guide](../filament-navigation-structure.md) and the [navigation group compatibility rule](../filament-v4-navigation-group-rule.md).
