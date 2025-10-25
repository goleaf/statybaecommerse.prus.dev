<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Document;
use App\Models\DocumentTemplate;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Stringable;
use Tests\TestCase;

final class DocumentTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_document_template_with_valid_data(): void
    {
        // Create a template instance to assert the core attributes persist correctly.
        $template = DocumentTemplate::create([
            'name'        => 'Invoice Template',
            'description' => 'Standard invoice template',
            'content'     => '<h1>Invoice #{{ORDER_NUMBER}}</h1>',
            'variables'   => ['ORDER_NUMBER', 'CUSTOMER_NAME', 'ORDER_TOTAL'],
            'type'        => 'invoice',
            'category'    => 'sales',
            'settings'    => ['page_size' => 'A4', 'orientation' => 'portrait'],
            'is_active'   => true,
        ]);

        expect($template)->toBeInstanceOf(DocumentTemplate::class);
        expect($template->slug)->toBe('invoice-template');
        expect($template->is_active)->toBeTrue();
    }

    public function test_fillable_attributes_are_explicit(): void
    {
        // Pull the fillable array to ensure guarded state stays predictable during mass assignment.
        $template = new DocumentTemplate;

        expect($template->getFillable())->toBe([
            'name',
            'slug',
            'description',
            'content',
            'variables',
            'type',
            'category',
            'settings',
            'is_active',
        ]);
    }

    public function test_slug_is_generated_when_missing(): void
    {
        // Persist without slug to confirm the model boot hook handles slug generation.
        $template = DocumentTemplate::create([
            'name'     => 'My Custom Template',
            'content'  => '<h1>Test</h1>',
            'type'     => 'receipt',
            'category' => 'sales',
        ]);

        expect($template->slug)->toBe('my-custom-template');
    }

    public function test_slug_respects_custom_value_when_provided(): void
    {
        // Provide a slug explicitly so the boot hook does not override it.
        $template = DocumentTemplate::create([
            'name'     => 'My Custom Template',
            'slug'     => 'custom-slug',
            'content'  => '<h1>Test</h1>',
            'type'     => 'receipt',
            'category' => 'sales',
        ]);

        expect($template->slug)->toBe('custom-slug');
    }

    public function test_slug_updates_when_name_changes_and_slug_is_cleared(): void
    {
        // Force a name change with an empty slug to verify the update listener regenerates it.
        $template = DocumentTemplate::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name',
        ]);

        $template->update([
            'name' => 'Updated Name',
            'slug' => '',
        ]);

        expect($template->fresh()->slug)->toBe('updated-name');
    }

    public function test_slug_does_not_change_when_value_exists(): void
    {
        // Update the name but keep the slug populated to ensure it remains stable.
        $template = DocumentTemplate::factory()->create([
            'name' => 'Original Name',
            'slug' => 'existing-slug',
        ]);

        $template->update(['name' => 'Updated Name']);

        expect($template->fresh()->slug)->toBe('existing-slug');
    }

    public function test_variables_cast_to_array(): void
    {
        // Persist an array of variables to confirm the cast stays intact.
        $template = DocumentTemplate::factory()->create([
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME', 'TOTAL'],
        ]);

        expect($template->variables)->toBeArray();
        expect($template->variables)->toContain('ORDER_NUMBER');
        expect($template->variables)->toContain('CUSTOMER_NAME');
    }

    public function test_settings_cast_to_array(): void
    {
        // Persist structured settings so the cast converts JSON payloads to arrays seamlessly.
        $template = DocumentTemplate::factory()->create([
            'settings' => ['page_size' => 'A4', 'margins' => '10mm'],
        ]);

        expect($template->settings)->toBeArray();
        expect($template->settings['page_size'])->toBe('A4');
        expect($template->settings['margins'])->toBe('10mm');
    }

    public function test_is_active_casts_to_boolean(): void
    {
        // Toggle the is_active flag with integer values to ensure boolean casting works.
        $activeTemplate = DocumentTemplate::factory()->create(['is_active' => 1]);
        $inactiveTemplate = DocumentTemplate::factory()->create(['is_active' => 0]);

        expect($activeTemplate->is_active)->toBeTrue();
        expect($inactiveTemplate->is_active)->toBeFalse();
    }

    public function test_documents_relationship_returns_all_related_records(): void
    {
        // Create three documents to confirm the relationship bypasses global scopes and returns everything.
        $template = DocumentTemplate::factory()->create();
        Document::factory()->count(3)->create([
            'document_template_id' => $template->id,
        ]);

        $template->load('documents');

        expect($template->documents)->toHaveCount(3);
        expect($template->documents->first())->toBeInstanceOf(Document::class);
    }

    public function test_scope_active_filters_inactive_templates(): void
    {
        // Seed mixed active states so the scope can be asserted.
        DocumentTemplate::factory()->create(['is_active' => true]);
        DocumentTemplate::factory()->create(['is_active' => false]);
        DocumentTemplate::factory()->create(['is_active' => true]);

        $activeTemplates = DocumentTemplate::active()->get();

        expect($activeTemplates)->toHaveCount(2);
        expect($activeTemplates->every(fn (DocumentTemplate $template) => $template->is_active))->toBeTrue();
    }

    public function test_scope_of_type_filters_by_type(): void
    {
        // Create multiple templates so only the requested type is returned.
        DocumentTemplate::factory()->create(['type' => 'invoice']);
        DocumentTemplate::factory()->create(['type' => 'receipt']);
        DocumentTemplate::factory()->create(['type' => 'invoice']);

        $invoiceTemplates = DocumentTemplate::ofType('invoice')->get();
        $receiptTemplates = DocumentTemplate::ofType('receipt')->get();

        expect($invoiceTemplates)->toHaveCount(2);
        expect($receiptTemplates)->toHaveCount(1);
    }

    public function test_scope_of_category_filters_by_category(): void
    {
        // Seed templates in different categories to ensure the scope works with NULL-safe comparisons.
        DocumentTemplate::factory()->create(['category' => 'sales']);
        DocumentTemplate::factory()->create(['category' => 'marketing']);
        DocumentTemplate::factory()->create(['category' => 'sales']);

        $salesTemplates = DocumentTemplate::ofCategory('sales')->get();
        $marketingTemplates = DocumentTemplate::ofCategory('marketing')->get();

        expect($salesTemplates)->toHaveCount(2);
        expect($marketingTemplates)->toHaveCount(1);
    }

    public function test_scope_by_type_aliases_scope_of_type(): void
    {
        // Verify the convenience scope mirrors ofType for DX consistency.
        DocumentTemplate::factory()->count(2)->create(['type' => 'invoice']);
        DocumentTemplate::factory()->create(['type' => 'receipt']);

        $invoiceTemplates = DocumentTemplate::byType('invoice')->get();

        expect($invoiceTemplates)->toHaveCount(2);
    }

    public function test_scope_by_category_aliases_scope_of_category(): void
    {
        // Ensure the alias scope provides the same filtering semantics.
        DocumentTemplate::factory()->count(2)->create(['category' => 'sales']);
        DocumentTemplate::factory()->create(['category' => 'marketing']);

        $salesTemplates = DocumentTemplate::byCategory('sales')->get();

        expect($salesTemplates)->toHaveCount(2);
    }

    public function test_scope_ordered_by_name_sorts_alphabetically(): void
    {
        // Create intentionally unsorted names and assert the resulting order is alphabetical.
        DocumentTemplate::factory()->create(['name' => 'Zulu Template']);
        DocumentTemplate::factory()->create(['name' => 'Alpha Template']);
        DocumentTemplate::factory()->create(['name' => 'Mike Template']);

        $orderedNames = DocumentTemplate::orderedByName()->pluck('name')->values();

        expect($orderedNames->all())->toBe([
            'Alpha Template',
            'Mike Template',
            'Zulu Template',
        ]);
    }

    public function test_get_available_variables_returns_configured_variables(): void
    {
        // Persist the variables array and ensure the accessor returns the same structure.
        $variables = ['ORDER_NUMBER', 'CUSTOMER_NAME', 'ORDER_TOTAL'];
        $template = DocumentTemplate::factory()->create([
            'variables' => $variables,
        ]);

        expect($template->getAvailableVariables())->toBe($variables);
    }

    public function test_get_available_variables_returns_empty_array_for_null(): void
    {
        // Ensure null values do not break consumers expecting an array.
        $template = DocumentTemplate::factory()->create([
            'variables' => null,
        ]);

        expect($template->getAvailableVariables())->toBe([]);
    }

    public function test_has_variable_detects_presence(): void
    {
        // Provide a simple list so we can assert both positive and negative lookups.
        $template = DocumentTemplate::factory()->create([
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME'],
        ]);

        expect($template->hasVariable('ORDER_NUMBER'))->toBeTrue();
        expect($template->hasVariable('CUSTOMER_NAME'))->toBeTrue();
        expect($template->hasVariable('NONEXISTENT'))->toBeFalse();
    }

    public function test_get_settings_returns_array(): void
    {
        // Persist settings and confirm the helper proxies the cast correctly.
        $settings = ['page_size' => 'A4', 'orientation' => 'portrait'];
        $template = DocumentTemplate::factory()->create([
            'settings' => $settings,
        ]);

        expect($template->getSettings())->toBe($settings);
    }

    public function test_get_setting_returns_value_or_default(): void
    {
        // Pull both existing and missing keys to verify the default fallbacks.
        $template = DocumentTemplate::factory()->create([
            'settings' => ['page_size' => 'A4'],
        ]);

        expect($template->getSetting('page_size'))->toBe('A4');
        expect($template->getSetting('orientation', 'portrait'))->toBe('portrait');
        expect($template->getSetting('nonexistent'))->toBeNull();
    }

    public function test_get_print_settings_merges_defaults_with_settings(): void
    {
        // Override select settings and confirm defaults remain present for missing keys.
        $template = DocumentTemplate::factory()->create([
            'settings' => [
                'page_size' => 'A5',
                'margins'   => ['top' => 10],
            ],
        ]);

        $printSettings = $template->getPrintSettings();

        expect($printSettings['page_size'])->toBe('A5');
        expect($printSettings['margins']['top'])->toBe(10);
        expect($printSettings['margins']['left'])->toBe(20);
    }

    public function test_render_replaces_scalar_variables(): void
    {
        // Feed scalar variables and ensure the placeholders are substituted correctly.
        $template = DocumentTemplate::factory()->create([
            'content' => 'Hello {{CUSTOMER_NAME}}, your order {{ORDER_NUMBER}} total is {{ORDER_TOTAL}}',
        ]);

        $rendered = $template->render([
            'CUSTOMER_NAME' => 'John Doe',
            'ORDER_NUMBER'  => '12345',
            'ORDER_TOTAL'   => '€100.00',
        ]);

        expect($rendered)->toBe('Hello John Doe, your order 12345 total is €100.00');
    }

    public function test_render_ignores_non_scalar_values(): void
    {
        // Include non-scalar values so the method leaves unknown placeholders untouched.
        $template = DocumentTemplate::factory()->create([
            'content' => 'Values: {{SCALAR}} {{OBJECT}}',
        ]);

        $rendered = $template->render([
            'SCALAR' => 'value',
            'OBJECT' => new class implements Stringable
            {
                public function __toString(): string
                {
                    // Return a simple string to confirm stringable objects are supported.
                    return 'stringable';
                }
            },
            'ARRAY'      => ['not', 'allowed'],
            'NULL_VALUE' => null,
        ]);

        expect($rendered)->toBe('Values: value stringable');
    }

    public function test_validation_prevents_missing_required_fields(): void
    {
        // Creating an empty record should fail because of database constraints.
        $this->expectException(QueryException::class);

        DocumentTemplate::create([]);
    }

    public function test_unique_slug_constraint_is_enforced(): void
    {
        // Seed a slug first, then attempt a duplicate to assert the database throws an error.
        DocumentTemplate::factory()->create([
            'slug' => 'unique-template',
        ]);

        $this->expectException(QueryException::class);

        DocumentTemplate::create([
            'name'     => 'Another Template',
            'slug'     => 'unique-template',
            'content'  => '<h1>Test</h1>',
            'type'     => 'invoice',
            'category' => 'sales',
        ]);
    }

    public function test_slug_regeneration_uses_str_slug_helpers(): void
    {
        // Provide a name with special characters to confirm Str::slug compatibility.
        $template = DocumentTemplate::create([
            'name'     => 'Ąžuolas Invoice #2025',
            'content'  => '<h1>Test</h1>',
            'type'     => 'invoice',
            'category' => 'sales',
        ]);

        expect($template->slug)->toBe(Str::slug('Ąžuolas Invoice #2025'));
    }
}
