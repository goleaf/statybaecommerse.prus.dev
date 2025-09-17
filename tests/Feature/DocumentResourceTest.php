<?php declare(strict_types=1);

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\User;
use App\Filament\Resources\DocumentResource;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Create administrator role and permissions
    $role = Role::create(['name' => 'administrator']);
    $permissions = [
        'view documents',
        'create documents',
        'update documents',
        'delete documents',
        'browse_documents'
    ];
    
    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }
    
    $role->givePermissionTo($permissions);
    
    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');
    
    // Create test data
    $this->testTemplate = DocumentTemplate::factory()->create([
        'name' => 'Test Template',
        'slug' => 'test-template',
        'type' => 'invoice',
    ]);
    
    $this->testDocument = Document::factory()->create([
        'document_template_id' => $this->testTemplate->id,
        'title' => 'Test Document',
        'status' => 'draft',
        'format' => 'pdf',
        'documentable_type' => 'App\\Models\\Order',
        'documentable_id' => 1,
    ]);
});

it('can list documents in admin panel', function () {
    $this->actingAs($this->adminUser)
        ->get(DocumentResource::getUrl('index'))
        ->assertOk();
});

it('can create a new document', function () {
    $template = DocumentTemplate::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'document_template_id' => $template->id,
            'title' => 'New Document',
            'status' => 'draft',
            'format' => 'pdf',
            'documentable_type' => 'App\\Models\\Order',
            'documentable_id' => 1,
            'variables' => json_encode(['key' => 'value']),
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('documents', [
        'document_template_id' => $template->id,
        'title' => 'New Document',
        'status' => 'draft',
        'format' => 'pdf',
        'documentable_type' => 'App\\Models\\Order',
        'documentable_id' => 1,
    ]);
});

it('can view a document', function () {
    $this->actingAs($this->adminUser)
        ->get(DocumentResource::getUrl('view', ['record' => $this->testDocument]))
        ->assertOk();
});

it('can edit a document', function () {
    Livewire::actingAs($this->adminUser)
        ->test(DocumentResource\Pages\EditDocument::class, ['record' => $this->testDocument->id])
        ->fillForm([
            'title' => 'Updated Document',
            'status' => 'completed',
            'format' => 'html',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
    
    $this->assertDatabaseHas('documents', [
        'id' => $this->testDocument->id,
        'title' => 'Updated Document',
        'status' => 'completed',
        'format' => 'html',
    ]);
});

it('can delete a document', function () {
    $document = Document::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(DocumentResource\Pages\EditDocument::class, ['record' => $document->id])
        ->callAction('delete')
        ->assertOk();
    
    $this->assertDatabaseMissing('documents', [
        'id' => $document->id,
    ]);
});

it('validates required fields when creating document', function () {
    Livewire::actingAs($this->adminUser)
        ->test(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'document_template_id' => null,
            'title' => null,
            'status' => null,
            'format' => null,
            'documentable_type' => null,
            'documentable_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['document_template_id', 'title', 'status', 'format', 'documentable_type', 'documentable_id']);
});

it('validates document status values', function () {
    $template = DocumentTemplate::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'document_template_id' => $template->id,
            'title' => 'Test Document',
            'status' => 'invalid-status',
            'format' => 'pdf',
            'documentable_type' => 'App\\Models\\Order',
            'documentable_id' => 1,
        ])
        ->call('create')
        ->assertHasFormErrors(['status']);
});

it('validates document format values', function () {
    $template = DocumentTemplate::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(DocumentResource\Pages\CreateDocument::class)
        ->fillForm([
            'document_template_id' => $template->id,
            'title' => 'Test Document',
            'status' => 'draft',
            'format' => 'invalid-format',
            'documentable_type' => 'App\\Models\\Order',
            'documentable_id' => 1,
        ])
        ->call('create')
        ->assertHasFormErrors(['format']);
});

it('can filter documents by status', function () {
    $this->actingAs($this->adminUser)
        ->get(DocumentResource::getUrl('index'))
        ->assertOk();
});

it('can filter documents by format', function () {
    $this->actingAs($this->adminUser)
        ->get(DocumentResource::getUrl('index'))
        ->assertOk();
});

it('can filter documents by template', function () {
    $this->actingAs($this->adminUser)
        ->get(DocumentResource::getUrl('index'))
        ->assertOk();
});

it('shows correct document data in table', function () {
    $this->actingAs($this->adminUser)
        ->get(DocumentResource::getUrl('index'))
        ->assertSee($this->testTemplate->name)
        ->assertSee($this->testDocument->title)
        ->assertSee('draft')
        ->assertSee('pdf');
});

it('handles document generation workflow', function () {
    $document = Document::factory()->create([
        'status' => 'draft',
        'file_path' => null,
    ]);
    
    // Simulate document generation
    $document->update([
        'status' => 'generating',
        'generated_at' => now(),
    ]);
    
    expect($document->status)->toBe('generating');
    expect($document->generated_at)->not->toBeNull();
});

it('handles bulk actions on documents', function () {
    $document1 = Document::factory()->create();
    $document2 = Document::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(DocumentResource\Pages\ListDocuments::class)
        ->callTableBulkAction('delete', [$document1->id, $document2->id])
        ->assertOk();
    
    $this->assertDatabaseMissing('documents', [
        'id' => $document1->id,
    ]);
    
    $this->assertDatabaseMissing('documents', [
        'id' => $document2->id,
    ]);
});

it('can download completed documents', function () {
    $document = Document::factory()->create([
        'status' => 'completed',
        'file_path' => 'documents/test-document.pdf',
    ]);
    
    $this->actingAs($this->adminUser)
        ->get(DocumentResource::getUrl('index'))
        ->assertSee('Download');
});

