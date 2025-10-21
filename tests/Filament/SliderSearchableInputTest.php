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

it('clears the quick action link state and payload when the lookup is emptied', function (): void {
    [$component, $livewire, $set] = resolveQuickActionComponent();

    seedButtonSelection($component, $livewire);

    $closure = resolveAfterStateUpdatedHook($component);
    $closure($component, null, $set);

    expect(data_get($livewire, 'button_url'))->toBeNull();
    expect(data_get($livewire, 'button_url_payload'))->toBe([]);
    expect($component->getOptions())->toBe([]);
    if ($component->hasMeta('payload')) {
        expect($component->getMeta('payload'))->toBe([]);
    }
    expect($set->values['button_url_payload'] ?? null)->toBe([]);
});

it('clears the management form link state and payload when the lookup is emptied', function (): void {
    [$component, $livewire, $set] = resolveManagementComponent();

    seedButtonSelection($component, $livewire);

    $closure = resolveAfterStateUpdatedHook($component);
    $closure($component, null, $set);

    expect(data_get($livewire, 'button_url'))->toBeNull();
    expect(data_get($livewire, 'button_url_payload'))->toBe([]);
    expect($component->getOptions())->toBe([]);
    if ($component->hasMeta('payload')) {
        expect($component->getMeta('payload'))->toBe([]);
    }
    expect($set->values['button_url_payload'] ?? null)->toBe([]);
});

it('clears the resource form link state and payload when the lookup is emptied', function (): void {
    [$component, $livewire, $set] = resolveResourceComponent();

    seedButtonSelection($component, $livewire);

    $closure = resolveAfterStateUpdatedHook($component);
    $closure($component, null, $set);

    expect(data_get($livewire, 'button_url'))->toBeNull();
    expect(data_get($livewire, 'button_url_payload'))->toBe([]);
    expect($component->getOptions())->toBe([]);
    if ($component->hasMeta('payload')) {
        expect($component->getMeta('payload'))->toBe([]);
    }
    expect($set->values['button_url_payload'] ?? null)->toBe([]);
});

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
    $component = resolveSearchableComponent($schema, 'button_url');

    return [$component, $livewire, new RecordingSet($component)];
}

function seedButtonSelection(SearchableInput $component, DummyLivewireComponent $livewire): void
{
    data_set($livewire, 'button_url', 'https://example.com');
    data_set($livewire, 'button_url_payload', ['cached' => true]);

    $component->state('https://example.com');
    $component->options(['https://example.com' => 'Example']);

    if (method_exists($component, 'payload')) {
        $component->payload(['cached' => true]);
    }
}

function resolveAfterStateUpdatedHook(SearchableInput $component): Closure
{
    $reflection = new ReflectionClass($component);
    $property = $reflection->getProperty('afterStateUpdated');
    $property->setAccessible(true);

    /** @var array<int, Closure> $callbacks */
    $callbacks = $property->getValue($component);

    return $callbacks[array_key_last($callbacks)];
}

function resolveSearchableComponent(Schema|Form $schema, string $statePath): SearchableInput
{
    $components = $schema->getFlatComponents(withActions: false, withHidden: true);

    $component = collect($components)->first(function ($component) use ($statePath): bool {
        return $component instanceof SearchableInput
            && $component->getStatePath() === $statePath;
    });

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

    public function render()
    {
        return view('filament::components.badge')->with(['badge' => '']);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->store[$name] = $value;
    }

    public function __get(string $name): mixed
    {
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
