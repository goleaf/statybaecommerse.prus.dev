<?php declare(strict_types=1);

use App\Filament\Resources\DocumentTemplateResource;
use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::factory()->create(['is_admin' => true]);
});

it('can list document templates in admin panel', function () {
    $documentTemplate = DocumentTemplate::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->assertCanSeeTableRecords([$documentTemplate]);
});

it('can create a new document template', function () {
    $documentTemplateData = [
        'name' => 'Test Template',
        'slug' => 'test-template',
        'description' => 'A test document template',
        'content' => '<p>Test content</p>',
        'type' => 'invoice',
        'category' => 'financial',
        'is_active' => true,
    ];

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
        ->fillForm($documentTemplateData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('document_templates', [
        'name' => 'Test Template',
        'slug' => 'test-template',
        'type' => 'invoice',
        'category' => 'financial',
        'is_active' => true,
    ]);
});

it('can view document template details', function () {
    $documentTemplate = DocumentTemplate::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ViewDocumentTemplate::class, ['record' => $documentTemplate->id])
        ->assertCanSeeTableRecords([$documentTemplate]);
});

it('can edit document template', function () {
    $documentTemplate = DocumentTemplate::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\EditDocumentTemplate::class, ['record' => $documentTemplate->id])
        ->fillForm([
            'name' => 'Updated Template',
            'description' => 'Updated description',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('document_templates', [
        'id' => $documentTemplate->id,
        'name' => 'Updated Template',
        'description' => 'Updated description',
    ]);
});

it('can filter document templates by type', function () {
    DocumentTemplate::factory()->invoice()->create();
    DocumentTemplate::factory()->receipt()->create();

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->filterTable('type', 'invoice')
        ->assertCanSeeTableRecords(DocumentTemplate::where('type', 'invoice')->get())
        ->assertCanNotSeeTableRecords(DocumentTemplate::where('type', 'receipt')->get());
});

it('can filter document templates by category', function () {
    DocumentTemplate::factory()->contract()->create();
    DocumentTemplate::factory()->quote()->create();

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->filterTable('category', 'legal')
        ->assertCanSeeTableRecords(DocumentTemplate::where('category', 'legal')->get())
        ->assertCanNotSeeTableRecords(DocumentTemplate::where('category', 'marketing')->get());
});

it('can filter document templates by active status', function () {
    DocumentTemplate::factory()->active()->create();
    DocumentTemplate::factory()->inactive()->create();

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->filterTable('is_active', '1')
        ->assertCanSeeTableRecords(DocumentTemplate::where('is_active', true)->get())
        ->assertCanNotSeeTableRecords(DocumentTemplate::where('is_active', false)->get());
});

it('can search document templates by name', function () {
    DocumentTemplate::factory()->create(['name' => 'Invoice Template']);
    DocumentTemplate::factory()->create(['name' => 'Receipt Template']);

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->searchTable('Invoice')
        ->assertCanSeeTableRecords(DocumentTemplate::where('name', 'like', '%Invoice%')->get())
        ->assertCanNotSeeTableRecords(DocumentTemplate::where('name', 'like', '%Receipt%')->get());
});

it('can search document templates by slug', function () {
    DocumentTemplate::factory()->create(['slug' => 'invoice-template']);
    DocumentTemplate::factory()->create(['slug' => 'receipt-template']);

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->searchTable('invoice')
        ->assertCanSeeTableRecords(DocumentTemplate::where('slug', 'like', '%invoice%')->get())
        ->assertCanNotSeeTableRecords(DocumentTemplate::where('slug', 'like', '%receipt%')->get());
});

it('can delete document template', function () {
    $documentTemplate = DocumentTemplate::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\EditDocumentTemplate::class, ['record' => $documentTemplate->id])
        ->callAction('delete')
        ->assertHasNoActionErrors();

    $this->assertDatabaseMissing('document_templates', [
        'id' => $documentTemplate->id,
    ]);
});

it('can bulk delete document templates', function () {
    $documentTemplates = DocumentTemplate::factory()->count(3)->create();

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->callTableBulkAction('delete', $documentTemplates)
        ->assertHasNoTableBulkActionErrors();

    foreach ($documentTemplates as $documentTemplate) {
        $this->assertDatabaseMissing('document_templates', [
            'id' => $documentTemplate->id,
        ]);
    }
});

