<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CollectionRuleResource;
use Filament\Forms\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Filament\FakeFormComponent;
use Tests\TestCase;

/**
 * Audits the collection rule resource schema to guarantee rule builders expose automation fields.
 */
final class CollectionRuleResourceSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_rule_form_includes_required_fields(): void
    {
        $form = CollectionRuleResource::form(Form::make(new FakeFormComponent));
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
