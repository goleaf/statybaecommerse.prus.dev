<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\DocumentTemplateType;
use App\Filament\Resources\DocumentTemplateResource\Pages\ListDocumentTemplates;
use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class DocumentTemplateResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel to mirror authenticated UI navigation.
        $this->resolveAdminPanel();

        // Standardise locales so translated labels remain predictable during assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Log in an administrator account for consistent resource access control.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_document_templates(): void
    {
        // Persist an invoice template so the listing has a deterministic row to render.
        $template = DocumentTemplate::factory()->invoice()->create([
            'name' => 'Invoice Blueprint',
        ]);

        Livewire::test(ListDocumentTemplates::class)
            // Trigger Filament's deferred loading hook before interacting with the table state.
            ->call('loadTable')
            // Confirm the created invoice template is surfaced to administrators.
            ->assertCanSeeTableRecords([$template])
            ->assertSee('Invoice Blueprint');
    }

    public function test_can_filter_document_templates_by_type(): void
    {
        // Create contrasting template types to exercise the type filter behaviour.
        $invoiceTemplate = DocumentTemplate::factory()->invoice()->create([
            'name' => 'Invoice Layout',
        ]);
        $contractTemplate = DocumentTemplate::factory()->contract()->create([
            'name' => 'Contract Layout',
        ]);

        Livewire::test(ListDocumentTemplates::class)
            // Hydrate the Livewire table prior to applying Filament filters.
            ->call('loadTable')
            // Narrow the dataset to invoice templates to ensure the filter works as expected.
            ->filterTable('type', DocumentTemplateType::Invoice->value)
            // Validate that only invoice templates remain visible and contract templates are hidden.
            ->assertCanSeeTableRecords([$invoiceTemplate])
            ->assertCanNotSeeTableRecords([$contractTemplate]);
    }
}
