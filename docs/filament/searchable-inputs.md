# Searchable input helper usage

The `App\\Support\\Filament\\SearchableComponentHelper` centralises the repetitive wiring required to keep [DefStudio's `SearchableInput`](https://github.com/defstudio/filament-searchable-input) fields hydrated with the correct state, options, and payload metadata inside our Filament forms. Use it whenever a Filament form component needs to look up a record, expose a human-readable label, and share structured payload data with sibling inputs.

## Hydrating a component

```php
use App\Support\Filament\SearchableComponentHelper;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Components\Hidden;

SearchableInput::make('user_lookup')
    ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
        SearchableComponentHelper::hydrate(
            $component,
            $state,
            fn (int $identifier): ?User => User::query()->with('profile')->find($identifier),
            static fn (User $user): array => [
                'value' => $user->getKey(),
                'label' => sprintf('%s <%s>', $user->name, $user->email),
                'payload' => [
                    'phone' => $user->profile?->phone,
                    'company' => $user->profile?->company,
                ],
            ],
        );
    });

Hidden::make('user_lookup_payload')
    ->default([])
    ->dehydrated(false); // Cache the lookup metadata for sibling components without persisting it.
```

1. **Lookup closure** – receives the persisted state and returns the matched record (or `null` when nothing should hydrate).
2. **Payload normaliser** – receives the resolved record and must return an array with:
   - `value`: the identifier that should be stored as component state.
   - `label`: the display text shown inside the dropdown.
   - `payload` (optional): any associative array that dependent fields can consume.

The helper converts the `value` to a string, registers it as the component state, and feeds the label through `options()` alongside the payload so downstream closures all read the same structure. When the normaliser returns an `Arrayable` payload (for example, a DTO implementing `toArray()`), the helper coerces it into an array before handing it over to Livewire. Empty or falsy identifiers short-circuit into `clear()` so the UI cannot surface stale metadata.

## Clearing a component

When a lookup is wiped out (for example, in an `afterStateUpdated` hook that receives a blank value), call the `clear()` helper to reset the state, options, and payload. Optional callbacks let you synchronise related form fields at the same time.

```php
use App\Support\Filament\SearchableComponentHelper;
use Filament\Forms\Get;
use Filament\Forms\Set;

SearchableInput::make('billing_address_lookup')
    ->afterStateUpdated(function (SearchableInput $component, ?string $state, Set $set, Get $get): void {
        if ($state !== null && $state !== '') {
            return;
        }

        SearchableComponentHelper::clear(
            $component,
            fn (): bool => $set('billing_address_id', null),
            fn (): bool => $set('billing_address_payload', []),
        );
    });
```

Each callback receives no arguments, so close over the Filament `Set`/`Get` helpers you need. Returning a value is optional; the helper ignores it after invocation.

Hidden payload fields (paired with `->dehydrated(false)`) keep the normalised metadata available to downstream components while ensuring `clear()` can wipe both the identifier and its cached payload in tandem.

## Normalisation tips

- Keep the payload structure aligned with the search service that powers the component. For example, `AddressSearch::payload()` already exposes the exact fields expected by the order form, so return it directly from your normaliser.
- When the component stores something other than the lookup identifier (for example, a composite key), make sure the `value` key reflects the final persisted state; the helper pushes that value back into the component before rendering.
- If a lookup fails or the state is empty, the helper automatically calls `clear()` so the UI stays in sync with the database.

## Resource integration checklist

- Register `afterStateHydrated` closures on your Filament form components to call `SearchableComponentHelper::hydrate()` with a finder closure and normaliser that return the `[value, label, payload]` tuple described above. This keeps edit forms and relation managers aligned when records are re-opened.
- Pair `afterStateUpdated` hooks with `SearchableComponentHelper::clear()` so clearing the lookup also wipes any dependent state (`Set` helpers for foreign keys, cached payload fields, and related dropdowns).
- Prefer returning a payload array that is already shaped for the downstream Livewire data structure you need. The helper simply forwards the normalised payload, making the component the single source of truth for metadata.

## Related guidelines

- Review the broader [searchable input metadata lifecycle](../forms/SEARCHABLE_INPUT_METADATA.md) for payload conventions and integration examples.
- Keep Filament resource ergonomics consistent by following the [navigation structure guide](../filament-navigation-structure.md) and the [navigation group compatibility rule](../filament-v4-navigation-group-rule.md).
