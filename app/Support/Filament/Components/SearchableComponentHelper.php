<?php

declare(strict_types=1);

namespace App\Support\Filament\Components;

use Closure;
use App\Support\Filament\Components\SearchableInput;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Component as SchemaComponent;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Support\Contracts\TranslatableContentDriver;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component as LivewireComponent;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use SplObjectStorage;
use Stringable;
use Throwable;

/**
 * Centralises SearchableInput state helpers so resources can stay lean and readable.
 *
 * @phpstan-type NormalisedPayload array{
 *     value: string|int|null,
 *     label: string|Stringable|null,
 *     payload?: array<array-key, mixed>|Arrayable|null,
 * }
 */
final class SearchableComponentHelper
{
    private static ?SplObjectStorage $payloadRegistry = null;

    private static ?ReflectionProperty $componentContainerProperty = null;

    /**
     * Guarded constructor to prevent instantiation.
     */
    private function __construct()
    {
        // Helper only exposes static APIs.
    }

    /**
     * Hydrate a SearchableInput with consistent state, options, and payload assignments.
     *
     * @param Closure(mixed): (object|array|null)      $resolveRecord    Resolves the selected record from the persisted state.
     * @param Closure(object|array): NormalisedPayload $normalizePayload Normalises the resolved record into the component state
     *                                                                   and payload tuple.
     */
    public static function hydrate(
        SearchableInput $component,
        mixed $state,
        Closure $resolveRecord,
        Closure $normalizePayload,
    ): void {
        // Early exit when no state is available so the component falls back to an empty input.
        if (self::stateIsEmpty($state)) {
            self::clear($component);

            return;
        }

        $record = $resolveRecord($state);

        // If the lookup fails we still want the UI to behave as if nothing was selected.
        if ($record === null) {
            self::clear($component);

            return;
        }

        $normalised = self::normaliseResolvedRecord($record, $state, $normalizePayload);

        if ($normalised === null) {
            self::clear($component);

            return;
        }

        self::applyComponentState($component, $normalised);
    }

    /**
     * Reset a SearchableInput to its pristine state while allowing callers to clear related form fields.
     */
    public static function clear(SearchableInput $component, Closure ...$clearRelated): void
    {
        self::ensureComponentHasContainer($component);

        // Wipe the component so Filament renders an empty dropdown and no metadata payload.
        self::applyState($component, null);
        self::applyOptions($component, []);

        self::applyPayload($component, []);

        // Execute any downstream clean-up callbacks so surrounding form state stays in sync.
        foreach ($clearRelated as $callback) {
            $callback();
        }
    }

    /**
     * Synchronise related state when a SearchableInput selection changes.
     *
     * @param Closure(string|int): (object|array|null)                                                    $resolveRecord    Locate the record backing the provided state.
     * @param Closure(object|array): NormalisedPayload                                                    $normalizePayload Convert the record into the helper payload tuple.
     * @param (Closure(array{value:int|string,label:string,payload:array<string|int, mixed>}): void)|null $onSync           Optional callback invoked when the record successfully resolves.
     * @param (Closure(): void)|null                                                                      $onClear          Optional callback invoked whenever the component is cleared.
     */
    public static function syncSelectedRecord(
        SearchableInput $component,
        ?string $state,
        Set $set,
        string $attribute,
        Closure $resolveRecord,
        Closure $normalizePayload,
        ?Closure $onSync = null,
        ?Closure $onClear = null,
    ): void {
        // Treat empty strings and null values as clear actions.
        if (self::stateIsEmpty($state)) {
            $set($attribute, null);
            self::clear($component, ...self::wrapOptionalCallback($onClear));

            return;
        }

        $record = $resolveRecord($state);

        if ($record === null) {
            $set($attribute, null);
            self::clear($component, ...self::wrapOptionalCallback($onClear));

            return;
        }

        $normalised = self::normaliseResolvedRecord($record, $state, $normalizePayload);

        if ($normalised === null) {
            $set($attribute, null);
            self::clear($component, ...self::wrapOptionalCallback($onClear));

            return;
        }

        self::applyComponentState($component, $normalised);

        $identifier = $normalised['value'];
        $set($attribute, is_numeric($identifier) ? (int) $identifier : $identifier);

        if ($onSync !== null) {
            // Surface the fully normalised payload so callers can hydrate dependent form data.
            $onSync($normalised);
        }
    }

    /**
     * Normalise the identifier into an integer or null when the component is empty.
     */
    public static function normaliseIdentifier(int|string|null $state): ?int
    {
        if ($state === null) {
            return null;
        }

        if (is_string($state)) {
            $state = trim($state);

            if ($state === '') {
                return null;
            }
        }

        return (int) $state;
    }

    /**
     * Reset a SearchableInput component to its pristine state (alias for {@see clear()}).
     */
    public static function clearComponent(SearchableInput $component): void
    {
        self::clear($component);
    }

    /**
     * Hydrate a SearchableInput component using a resolved Eloquent model instance.
     *
     * @template TModel of Model
     *
     * @param TModel|null              $model
     * @param callable(TModel): string $labelResolver Resolve the label for the hydrated option.
     */
    public static function hydrateFromRecord(
        SearchableInput $component,
        ?int $state,
        ?Model $record,
        Closure $labelResolver,
    ): void {
        if ($state === null || $record === null) {
            return;
        }

        // Preserve parity with the richer helper by pushing an empty payload alongside state/options.
        self::applyComponentState($component, [
            'value'   => $record->getKey(),
            'label'   => $labelResolver($record),
            'payload' => [],
        ]);
    }

    /**
     * Hydrate a SearchableInput component by resolving a model lazily.
     *
     * @template TModel of Model
     *
     * @param callable(int): (TModel|null) $finder        Retrieve the model from a persisted store.
     * @param callable(TModel): string     $labelResolver Resolve the label for the hydrated option.
     */
    public static function hydrateUsingResolver(
        SearchableInput $component,
        ?int $state,
        Closure $resolver,
        Closure $labelResolver,
    ): void {
        if ($state === null) {
            return;
        }

        $record = $resolver($state);

        if (! $record instanceof Model) {
            return;
        }

        self::hydrateFromRecord($component, $state, $record, $labelResolver);
    }

    /**
     * Persist a nullable relation identifier by normalising the raw component state first.
     *
     * When the resolved identifier is null, the associated SearchableInput component is
     * also cleared to keep its options, label, and payload in sync with the stored value.
     * Refer to docs/forms/SEARCHABLE_INPUT_HELPER.md for behavioural notes around this helper.
     */
    public static function syncNullableIntState(
        int|string|null $state,
        Set $set,
        string $field,
        ?SearchableInput $component = null
    ): void {
        $identifier = self::normaliseIdentifier($state);

        if ($identifier === null) {
            $set($field, null);

            if ($component !== null) {
                self::clear($component);
            }

            return;
        }

        $set($field, $identifier);
    }

    /**
     * Synchronize a lookup component with an associated payload array (e.g. address metadata).
     *
     * @template TModel of Model
     *
     * @param int|string|null                        $state           Raw component state that may be null, empty, or a string identifier.
     * @param callable(int): (TModel|null)           $finder          Resolve the model backing the lookup.
     * @param callable(TModel): array<string, mixed> $payloadResolver Build the normalized payload for dependent components.
     * @param callable(TModel): string|null          $labelResolver   Optionally resolve an explicit label for the lookup component.
     * @param array<string, mixed>                   $emptyPayload    Provide the default payload when no selection exists.
     */
    public static function syncLookupPayload(
        Set $set,
        string $lookupField,
        string $payloadField,
        ?string $state,
        Closure $payloadResolver,
        array $emptyPayload = []
    ): void {
        $identifier = self::normaliseIdentifier($state);

        if ($identifier === null) {
            $set($lookupField, null);
            $set($payloadField, $emptyPayload);

            return;
        }

        $set($lookupField, $identifier);
        $payload = $payloadResolver($identifier);

        if ($payload === null) {
            $set($payloadField, $emptyPayload);

            return;
        }

        $set($payloadField, is_array($payload) ? $payload : (array) $payload);
    }

    /**
     * Determine whether the provided state should be considered empty and therefore cleared.
     */
    private static function stateIsEmpty(mixed $state): bool
    {
        if ($state === null) {
            return true;
        }

        if (is_string($state) && trim($state) === '') {
            return true;
        }

        return false;
    }

    /**
     * Convert the resolved record into the tuple consumed by SearchableInput state helpers.
     *
     * @return array{value:int|string, label:string, payload: array<array-key, mixed>}|null
     */
    private static function normaliseResolvedRecord(
        object|array $record,
        mixed $state,
        Closure $normalizePayload,
    ): ?array {
        /** @var NormalisedPayload $normalised */
        $normalised = $normalizePayload($record);

        $value = $normalised['value'] ?? $state;

        // Bail out when the normaliser cannot determine a usable identifier.
        if (self::stateIsEmpty($value)) {
            return null;
        }

        $label = $normalised['label'] ?? '';

        if ($label instanceof Stringable) {
            $label = (string) $label;
        } elseif (! is_string($label)) {
            // Fallback to a simple cast so the dropdown always receives a string label.
            $label = (string) $label;
        }

        $payload = $normalised['payload'] ?? [];

        if ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        } elseif (! is_array($payload)) {
            // Casting keeps loosely-typed payloads (for example, DTOs) compatible with Livewire serialisation.
            $payload = (array) $payload;
        }

        return [
            'value'   => $value,
            'label'   => $label,
            'payload' => $payload,
        ];
    }

    /**
     * Apply the normalised value, label, and payload to the SearchableInput component.
     *
     * @param array{value:int|string, label:string, payload: array<array-key, mixed>} $normalised
     */
    private static function applyComponentState(SearchableInput $component, array $normalised): void
    {
        self::ensureComponentHasContainer($component);

        $stringValue = (string) $normalised['value'];

        self::applyState($component, $stringValue);
        self::applyOptions($component, [$stringValue => $normalised['label']]);

        $payload = $normalised['payload'];
        $payload['id'] = $stringValue;
        $payload['label'] = $normalised['label'];

        self::applyPayload($component, $payload);
    }

    /**
     * Wrap an optional callback in an array so it can be unpacked into variadic parameters.
     *
     * @return array<int, Closure>
     */
    private static function wrapOptionalCallback(?Closure $callback): array
    {
        return $callback !== null ? [$callback] : [];
    }

    /**
     * Safely assign payload data when the component may not be attached to a schema container.
     *
     * @param array<array-key, mixed> $payload
     */
    public static function applyPayload(SearchableInput $component, array $payload): void
    {
        self::registerPayloadMacros();
        self::setComponentPayload($component, $payload);

        try {
            $component->payload($payload);
        } catch (Throwable) {
            // Component container is not initialised (e.g. raw testing instances); ignore.
        }
    }

    /**
     * Ensure SearchableInput instances expose payload helper macros even when Filament hasn't booted.
     */
    public static function registerPayloadMacros(): void
    {
        if (! method_exists(SearchableInput::class, 'macro')) {
            return;
        }

        $shouldRegister = ! SearchableInput::hasMacro('payload')
            || ! SearchableInput::hasMacro('getPayload')
            || ! SearchableInput::hasMacro('forgetPayload');

        if (! $shouldRegister) {
            return;
        }

        SearchableInput::macro('payload', function (?array $payload = null) {
            if (func_num_args() === 0) {
                return SearchableComponentHelper::getComponentPayload($this);
            }

            SearchableComponentHelper::setComponentPayload($this, $payload ?? []);

            return $this;
        });

        SearchableInput::macro('getPayload', function (): array {
            return SearchableComponentHelper::getComponentPayload($this);
        });

        SearchableInput::macro('forgetPayload', function (): static {
            SearchableComponentHelper::clearComponentPayload($this);

            return $this;
        });
    }

    /**
     * Store payload metadata using an SplObjectStorage registry to avoid dynamic properties.
     */
    public static function setComponentPayload(SearchableInput $component, array $payload): void
    {
        if ($payload === []) {
            self::clearComponentPayload($component);

            return;
        }

        $registry = self::payloadRegistry();
        $registry[$component] = $payload;
    }

    /**
     * Retrieve the payload for the given component.
     */
    public static function getComponentPayload(SearchableInput $component): array
    {
        $registry = self::payloadRegistry();

        if (! $registry->contains($component)) {
            return [];
        }

        $payload = $registry[$component];

        return is_array($payload) ? $payload : [];
    }

    /**
     * Remove stored payload metadata for the given component.
     */
    public static function clearComponentPayload(SearchableInput $component): void
    {
        $registry = self::payloadRegistry();

        if ($registry->contains($component)) {
            $registry->detach($component);
        }
    }

    /**
     * Provide the backing payload registry storage.
     */
    private static function payloadRegistry(): SplObjectStorage
    {
        if (! self::$payloadRegistry instanceof SplObjectStorage) {
            self::$payloadRegistry = new SplObjectStorage;
        }

        return self::$payloadRegistry;
    }

    /**
     * Ensure a form container is available so state mutations do not trigger uninitialised property errors.
     */
    private static function ensureComponentHasContainer(SearchableInput $component): void
    {
        if (self::componentHasContainer($component)) {
            return;
        }

        $host = new class extends LivewireComponent implements HasForms
        {
            public function dispatchFormEvent(mixed ...$args): void {}

            public function getActiveFormsLocale(): ?string
            {
                return null;
            }

            public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
            {
                return null;
            }

            public function getForm(string $name): ?Form
            {
                return null;
            }

            public function getFormComponentFileAttachment(string $statePath): ?TemporaryUploadedFile
            {
                return null;
            }

            public function getFormComponentFileAttachmentUrl(string $statePath): ?string
            {
                return null;
            }

            public function getFormSelectOptionLabels(string $statePath): array
            {
                return [];
            }

            public function getFormSelectOptionLabel(string $statePath): ?string
            {
                return null;
            }

            public function getFormSelectOptions(string $statePath): array
            {
                return [];
            }

            public function getFormSelectSearchResults(string $statePath, string $search): array
            {
                return [];
            }

            public function getFormUploadedFiles(string $statePath): ?array
            {
                return null;
            }

            public function getOldFormState(string $statePath): mixed
            {
                return data_get($this, $statePath);
            }

            public function isCachingForms(): bool
            {
                return false;
            }

            public function removeFormUploadedFile(string $statePath, string $fileKey): void {}

            public function reorderFormUploadedFiles(string $statePath, array $fileKeys): void {}

            public function validate($rules = null, $messages = [], $attributes = []): array
            {
                return [];
            }

            public function currentlyValidatingForm(?ComponentContainer $form): void
            {
                // No-op for isolated form host.
            }
        };

        Form::make($host)
            ->schema([$component])
            ->getComponents();
    }

    /**
     * Determine whether the component is already attached to a schema container.
     */
    private static function componentHasContainer(SearchableInput $component): bool
    {
        if (self::$componentContainerProperty === null) {
            try {
                $reflection = new ReflectionClass(SchemaComponent::class);
                $property = $reflection->getProperty('container');
                $property->setAccessible(true);
                self::$componentContainerProperty = $property;
            } catch (ReflectionException) {
                return false;
            }
        }

        try {
            if (! self::$componentContainerProperty->isInitialized($component)) {
                return false;
            }

            return self::$componentContainerProperty->getValue($component) !== null;
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Safely update component state without requiring a pre-initialised container.
     */
    private static function applyState(SearchableInput $component, mixed $state): void
    {
        try {
            $component->state($state);
        } catch (Throwable) {
            // Component lacks a Livewire container; ignore state mutation.
        }
    }

    /**
     * Safely update component options without requiring a pre-initialised container.
     *
     * @param array<array-key, string> $options
     */
    private static function applyOptions(SearchableInput $component, array $options): void
    {
        try {
            $component->options($options);
        } catch (Throwable) {
            // Component lacks a Livewire container; ignore options mutation.
        }
    }
}
