<?php

declare(strict_types=1);

namespace Tests\Filament;

use App\Filament\Pages\SliderManagement;
use App\Filament\Resources\SliderResource;
use App\Filament\Widgets\SliderQuickActionsWidget;
use Closure;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Form;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Set as BaseSet;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\TranslatableContentDriver;
use Livewire\Component as LivewireComponent;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

uses(TestCase::class);

// Provide each place where the slider link lookup exists so the shared assertion can exercise all entry points.
dataset('slider_searchable_input_resolvers', [
    'quick actions widget' => resolveQuickActionComponent(...),
    'management page'      => resolveManagementComponent(...),
    'resource form'        => resolveResourceComponent(...),
]);

it('clears the slider link lookup state and payload when the search input is emptied', function (SearchableInput $component, DummyLivewireComponent $livewire, RecordingSet $set): void {
    // Preload the component and Livewire store with a synthetic selection to mirror the persisted state users would have set previously.
    seedButtonSelection($component, $livewire);

    // Resolve the dynamically registered hook and simulate clearing the SearchableInput to trigger the cleanup logic.
    $closure = resolveAfterStateUpdatedHook($component);
    $closure($component, null, $set);

    assertSliderLookupCleared($component, $livewire, $set);
})->with('slider_searchable_input_resolvers');

/**
 * @return array{SearchableInput, DummyLivewireComponent, RecordingSet}
 */
function resolveQuickActionComponent(): array
{
    $widget = app(SliderQuickActionsWidget::class);
    $action = $widget->createSliderAction();

    return resolveComponentFromAction($action);
}

/**
 * @return array{SearchableInput, DummyLivewireComponent, RecordingSet}
 */
function resolveManagementComponent(): array
{
    $page = app(SliderManagement::class);
    $action = $page->createSliderAction();

    return resolveComponentFromAction($action);
}

/**
 * @return array{SearchableInput, DummyLivewireComponent, RecordingSet}
 */
function resolveResourceComponent(): array
{
    $livewire = new DummyLivewireComponent;
    $form = SliderResource::form(Form::make($livewire));

    $component = resolveSearchableComponent($form, 'button_url');

    return [$component, $livewire, new RecordingSet($component)];
}

/**
 * @return array{SearchableInput, DummyLivewireComponent, RecordingSet}
 */
function resolveComponentFromAction(Action $action): array
{
    $livewire = new DummyLivewireComponent;
    $schema = $action->getSchema(Form::make($livewire));

    // Fail fast if the action does not expose a schema instance, mirroring the runtime expectation for Filament actions.
    if (! $schema instanceof Schema) {
        throw new RuntimeException('Unable to resolve schema from slider action.');
    }

    $component = resolveSearchableComponent($schema, 'button_url');

    return [$component, $livewire, new RecordingSet($component)];
}

function seedButtonSelection(SearchableInput $component, DummyLivewireComponent $livewire): void
{
    // Seed both the Livewire backing store and the component so the after-update hook has data to remove.
    data_set($livewire, 'button_url', 'https://example.com');
    data_set($livewire, 'button_url_payload', ['cached' => true]);

    $component->state('https://example.com');
    $component->options(['https://example.com' => 'Example']);

    if (method_exists($component, 'payload')) {
        $component->payload(['cached' => true]);
    }
}

function assertSliderLookupCleared(SearchableInput $component, DummyLivewireComponent $livewire, RecordingSet $set): void
{
    // Confirm the Livewire component no longer exposes the stale link selection.
    expect(data_get($livewire, 'button_url'))->toBeNull();
    expect(data_get($livewire, 'button_url_payload'))->toBe([]);

    // Ensure the SearchableInput itself drops its cached options and payload metadata.
    expect($component->getOptions())->toBe([]);
    if ($component->hasMeta('payload')) {
        expect($component->getMeta('payload'))->toBe([]);
    }

    // Verify that Filament's Set helper recorded an empty payload for the field.
    expect($set->values['button_url_payload'] ?? null)->toBe([]);
}

/**
 * @return Closure(SearchableInput, mixed, RecordingSet): mixed
 */
function resolveAfterStateUpdatedHook(SearchableInput $component): Closure
{
    $reflection = new ReflectionClass($component);
    $property = $reflection->getProperty('afterStateUpdated');
    $property->setAccessible(true);

    /** @var array<int, Closure|callable> $callbacks */
    $callbacks = $property->getValue($component);

    if ($callbacks === []) {
        throw new RuntimeException('SearchableInput hook stack was empty.');
    }

    $callback = $callbacks[array_key_last($callbacks)];

    if (! $callback instanceof Closure) {
        throw new RuntimeException('SearchableInput hook stack contained a non-closure callback.');
    }

    return $callback;
}

function resolveSearchableComponent(Schema|Form $schema, string $statePath): SearchableInput
{
    $components = $schema->getFlatComponents(withActions: false, withHidden: true);

    $component = collect($components)->first(fn ($component): bool => $component instanceof SearchableInput
        && $component->getStatePath() === $statePath);

    return $component instanceof SearchableInput
        ? $component
        : throw new RuntimeException("Unable to resolve SearchableInput for [{$statePath}].");
}

/**
 * A lightweight Livewire component that satisfies Filament's schema contracts for testing.
 */
final class DummyLivewireComponent extends LivewireComponent implements HasSchemas
{
    /** @var array<string, mixed> */
    public array $store = [];

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    public function getOldSchemaState(string $statePath): mixed
    {
        return data_get($this, $statePath);
    }

    public function getSchemaComponent(string $key, bool $withHidden = false, ?Component $skipComponentChildContainersWhileSearching = null): Component|Action|ActionGroup|null
    {
        return null;
    }

    public function getSchema(string $name): ?Schema
    {
        return null;
    }

    public function currentlyValidatingSchema(?Schema $schema): void {}

    public function getDefaultTestingSchemaName(): ?string
    {
        return null;
    }

    public function render(): mixed
    {
        return view('filament::components.badge')->with(['badge' => '']);
    }

    public function __set(mixed $name, mixed $value): void
    {
        // Ensure array keys remain strings so the fake store mirrors Livewire's property bag behaviour.
        if (! is_string($name)) {
            throw new RuntimeException('DummyLivewireComponent expects string property names.');
        }

        $this->store[$name] = $value;
    }

    public function __get(mixed $name): mixed
    {
        if (! is_string($name)) {
            throw new RuntimeException('DummyLivewireComponent expects string property names.');
        }

        return $this->store[$name] ?? null;
    }
}

/**
 * Records Set invocations while still updating the underlying component state via the parent implementation.
 */
final class RecordingSet extends BaseSet
{
    /** @var array<string, mixed> */
    public array $values = [];

    public function __construct(SearchableInput $component)
    {
        parent::__construct($component);
    }

    public function __invoke(string|Component $path, mixed $state, bool $isAbsolute = false, bool $shouldCallUpdatedHooks = false): mixed
    {
        $key = $path instanceof Component ? $path->getStatePath() : $path;
        $this->values[$key] = $state;

        return parent::__invoke($path, $state, $isAbsolute, $shouldCallUpdatedHooks);
    }
}
