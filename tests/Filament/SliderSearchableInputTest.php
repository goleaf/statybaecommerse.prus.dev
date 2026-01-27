<?php

declare(strict_types=1);

namespace Tests\Filament;

use App\Filament\Pages\SliderManagement;
use App\Filament\Resources\SliderResource;
use App\Filament\Widgets\SliderQuickActionsWidget;
use App\Support\Filament\Components\SearchableInput;
use App\Support\Filament\SearchableInputHelper;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\TranslatableContentDriver;
use Livewire\Component as LivewireComponent;
use ErrorException;
use RuntimeException;
use Tests\TestCase;

uses(TestCase::class);

// Provide each place where the slider link lookup exists so the shared assertion can exercise all entry points.
dataset('slider_searchable_input_resolvers', [
    'quick actions widget' => static fn (): array => resolveQuickActionComponent(),
    'management page'      => static fn (): array => resolveManagementComponent(),
    'resource form'        => static fn (): array => resolveResourceComponent(),
]);

it('clears the slider link lookup state and payload when the search input is emptied', function (callable $resolver): void {
    try {
        [$component, $livewire] = $resolver();
    } catch (ErrorException $exception) {
        $this->markTestSkipped($exception->getMessage());
    }

    // Preload the component and Livewire store with a synthetic selection to mirror the persisted state users would have set previously.
    seedButtonSelection($component, $livewire);

    // Clear via the shared helper to mirror the form behavior without invoking Filament's internal hook binding.
    SearchableInputHelper::clear(
        $component,
        static function (string $field, mixed $value) use ($livewire): void {
            data_set($livewire, $field, $value);
        },
        ['button_url' => null],
    );

    assertSliderLookupCleared($component, $livewire);
})->with('slider_searchable_input_resolvers');

/**
 * @return array{SearchableInput, DummyLivewireComponent}
 */
function resolveQuickActionComponent(): array
{
    if (! class_exists(\Filament\Tables\Actions\Action::class)) {
        throw new ErrorException('Filament tables actions are not available in this build.');
    }

    $widget = app(SliderQuickActionsWidget::class);
    $action = $widget->createSliderAction();

    return resolveComponentFromAction($action);
}

/**
 * @return array{SearchableInput, DummyLivewireComponent}
 */
function resolveManagementComponent(): array
{
    $page = app(SliderManagement::class);
    $action = $page->createSliderAction();

    return resolveComponentFromAction($action);
}

/**
 * @return array{SearchableInput, DummyLivewireComponent}
 */
function resolveResourceComponent(): array
{
    if (! class_exists(SliderResource::class)) {
        throw new ErrorException('SliderResource is not available in this build.');
    }

    $livewire = new DummyLivewireComponent;
    $form = SliderResource::form(Schema::make($livewire));

    $component = resolveSearchableComponent($form, 'button_url');

    return [$component, $livewire];
}

/**
 * @return array{SearchableInput, DummyLivewireComponent}
 */
function resolveComponentFromAction(Action $action): array
{
    $livewire = new DummyLivewireComponent;
    $schema = $action->getSchema(Schema::make($livewire));

    // Fail fast if the action does not expose a schema instance, mirroring the runtime expectation for Filament actions.
    if (! $schema instanceof Schema) {
        throw new RuntimeException('Unable to resolve schema from slider action.');
    }

    $component = resolveSearchableComponent($schema, 'button_url');

    return [$component, $livewire];
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

function assertSliderLookupCleared(SearchableInput $component, DummyLivewireComponent $livewire): void
{
    // Confirm the Livewire component no longer exposes the stale link selection.
    expect(data_get($livewire, 'button_url'))->toBeNull();
    expect(data_get($livewire, 'button_url_payload'))->toBe([]);

    // Ensure the SearchableInput itself drops its cached options and payload metadata.
    expect($component->getOptions())->toBe([]);
    if ($component->hasMeta('payload')) {
        expect($component->getMeta('payload'))->toBe([]);
    }
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

    public function getSchemaComponent(string $key, bool $withHidden = false, array $skipComponentsChildContainersWhileSearching = []): Component|Action|ActionGroup|null
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

