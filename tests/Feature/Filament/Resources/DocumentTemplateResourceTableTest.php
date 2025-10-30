<?php

declare(strict_types=1);

use App\Filament\Resources\DocumentTemplateResource\Pages\ListDocumentTemplates;
use App\Filament\Resources\DocumentTemplateResource\RelationManagers\DocumentsRelationManager;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('duplicates a document template via the table action', function (): void {
    // Resolve the Filament admin panel to mimic the live environment behaviour for resource pages.
    $this->resolveAdminPanel();

    // Seed a privileged admin and a source template with deterministic identifiers for action assertions.
    $admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $template = DocumentTemplate::factory()->create([
        'name' => 'Coverage Template',
        'slug' => 'coverage-template',
    ]);

    actingAs($admin);

    // Trigger the duplicate action and ensure a cloned record persists with the expected slug suffix.
    Livewire::test(ListDocumentTemplates::class)
        ->call('loadTable')
        ->callTableAction('duplicate_template', $template)
        ->assertHasNoTableActionErrors();

    expect(DocumentTemplate::query()->where('slug', 'coverage-template-copy')->count())->toBe(1);
    expect(DocumentTemplate::query()->where('slug', 'coverage-template')->count())->toBe(1);
});

it('opens the preview table action without validation errors', function (): void {
    // Ensure Filament resolves the admin panel context before mounting Livewire components.
    $this->resolveAdminPanel();

    // Create an admin user and an easily asserted template payload for modal content checks.
    $admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $template = DocumentTemplate::factory()->create([
        'name'    => 'Preview Template',
        'slug'    => 'preview-template',
        'content' => 'Previewable content block',
    ]);

    actingAs($admin);

    // Mount the preview action to ensure the modal opens and the action executes cleanly.
    Livewire::test(ListDocumentTemplates::class)
        ->call('loadTable')
        ->mountTableAction('preview_template', $template)
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();
});

it('deactivates templates via the bulk action', function (): void {
    // Align the Filament panel for table hydration and permission resolution.
    $this->resolveAdminPanel();

    // Provision an admin and a single active template as the target for the deactivation workflow.
    $admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $activeTemplate = DocumentTemplate::factory()->create([
        'name'      => 'Active Template',
        'slug'      => 'active-template',
        'is_active' => true,
    ]);

    actingAs($admin);

    Livewire::test(ListDocumentTemplates::class)
        ->call('loadTable')
        ->callTableBulkAction('deactivate', [$activeTemplate])
        ->assertHasNoTableBulkActionErrors();

    expect($activeTemplate->refresh()->is_active)->toBeFalse();
});

it('lists related documents through the documents relation manager', function (): void {
    // Resolve the panel and authenticate so the relation manager honours Filament policies.
    $this->resolveAdminPanel();

    $admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $template = DocumentTemplate::factory()->create([
        'name' => 'Relation Host Template',
        'slug' => 'relation-host-template',
    ]);

    $document = Document::factory()->create([
        'document_template_id' => $template->id,
        'status'               => Document::STATUS_DRAFT,
    ]);

    actingAs($admin);

    // Mount the relation manager to ensure the associated document record appears within the table.
    Livewire::test(DocumentsRelationManager::class, [
        'ownerRecord' => $template,
        'pageClass'   => \App\Filament\Resources\DocumentTemplateResource\Pages\ViewDocumentTemplate::class,
    ])
        ->call('loadTable')
        ->assertCanSeeTableRecords([$document]);
});

it('filters related documents by status within the relation manager', function (): void {
    // Prepare the Filament admin panel and authenticate for relation manager interactions.
    $this->resolveAdminPanel();

    $admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $template = DocumentTemplate::factory()->create([
        'name' => 'Filter Host Template',
        'slug' => 'filter-host-template',
    ]);

    $draftDocument = Document::factory()->create([
        'document_template_id' => $template->id,
        'status'               => Document::STATUS_DRAFT,
    ]);

    $publishedDocument = Document::factory()->create([
        'document_template_id' => $template->id,
        'status'               => Document::STATUS_PUBLISHED,
    ]);

    actingAs($admin);

    // Apply the status filter and verify only the targeted record remains visible in the table output.
    Livewire::test(DocumentsRelationManager::class, [
        'ownerRecord' => $template,
        'pageClass'   => \App\Filament\Resources\DocumentTemplateResource\Pages\ViewDocumentTemplate::class,
    ])
        ->call('loadTable')
        ->filterTable('status', Document::STATUS_DRAFT)
        ->assertCanSeeTableRecords([$draftDocument])
        ->assertCanNotSeeTableRecords([$publishedDocument]);
});
