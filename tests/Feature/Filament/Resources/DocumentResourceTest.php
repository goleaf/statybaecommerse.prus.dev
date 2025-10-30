<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages\ListDocuments;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class DocumentResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Prime the Filament admin panel so page resolution mirrors production behaviour.
        $this->resolveAdminPanel();

        // Align application locale expectations with deterministic English fixtures.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate an administrator to satisfy Filament authorization gates.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_documents(): void
    {
        // Seed a draft document so the listing has a visible row to render.
        $document = Document::factory()->draft()->create([
            'title' => 'Coverage Document',
        ]);

        Livewire::test(ListDocuments::class)
            // Hydrate the table prior to assertions to emulate the deferred loading lifecycle.
            ->call('loadTable')
            // Confirm the freshly created document record is surfaced to administrators.
            ->assertCanSeeTableRecords([$document])
            ->assertSee('Coverage Document');
    }

    public function test_can_filter_documents_by_status(): void
    {
        // Create contrasting records so the status filter has deterministic expectations.
        $draftDocument = Document::factory()->draft()->create([
            'title' => 'Draft Handbook',
        ]);
        $generatedDocument = Document::factory()->generated()->create([
            'title' => 'Generated Checklist',
        ]);

        Livewire::test(ListDocuments::class)
            // Ensure the Livewire table data is loaded before applying filters.
            ->call('loadTable')
            // Apply the Filament status filter to isolate draft-only documents.
            ->filterTable('status', 'draft')
            // Verify the filtered dataset only includes the expected draft entry.
            ->assertCanSeeTableRecords([$draftDocument])
            ->assertCanNotSeeTableRecords([$generatedDocument]);
    }
}
