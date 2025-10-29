<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CollectionRuleResource;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Filament\FakeFormComponent;
use Tests\TestCase;
use TypeError;

/**
 * Audits the collection rule resource schema to guarantee rule builders expose automation fields.
 */
final class CollectionRuleResourceSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_rule_form_includes_required_fields(): void
    {
        // Attempt to build the schema using whatever Filament exposes in this environment;
        // older plugin aliases still point to the v3 Form component, which expects an array.
        try {
            $schema = class_exists(Form::class)
                ? Form::make(new FakeFormComponent)
                : Schema::make(new FakeFormComponent);
        } catch (TypeError $exception) {
            // Fallback for installations where Form::make() expects a schema array instead of a Livewire component.
            $schema = Schema::make(new FakeFormComponent);
        }

        $form = CollectionRuleResource::form($schema);
        $components = collect($form->getFlatComponents(withHidden: true, withActions: false));

        $statePaths = $components
            ->map(static fn ($component) => method_exists($component, 'getStatePath') ? $component->getStatePath() : null)
            ->filter()
            ->values();

        // Core rule builder controls should always be available for dynamic collection automation.
        $this->assertContains('collection_id', $statePaths->all());
        $this->assertContains('field', $statePaths->all());
        $this->assertContains('operator', $statePaths->all());
        $this->assertContains('value', $statePaths->all());
        $this->assertContains('position', $statePaths->all());
    }
}
