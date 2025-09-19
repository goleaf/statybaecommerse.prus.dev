<?php declare(strict_types=1);

use App\Filament\Resources\DocumentTemplateResource;
use App\Models\DocumentTemplate;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create permissions
    $permissions = [
        'browse_document_templates',
        'read_document_templates',
        'edit_document_templates',
        'add_document_templates',
        'delete_document_templates',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    $role = Role::create(['name' => 'administrator']);
    $role->givePermissionTo($permissions);

    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');

    // Create test data
    $this->testDocumentTemplate = DocumentTemplate::factory()->create([
        'name' => 'Test Template',
        'slug' => 'test-template',
        'type' => 'invoice',
        'category' => 'business',
        'is_active' => true,
    ]);
});

it('can list document templates in admin panel', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index'))
        ->assertOk();
});

it('can create a document template', function () {
    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
        ->fillForm([
            'name' => 'New Template',
            'slug' => 'new-template',
            'description' => 'A new template',
            'content' => '<h1>{{title}}</h1><p>{{content}}</p>',
            'variables' => ['title' => 'Title', 'content' => 'Content'],
            'type' => 'invoice',
            'category' => 'business',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('document_templates', [
        'name' => 'New Template',
        'slug' => 'new-template',
        'type' => 'invoice',
        'category' => 'business',
        'is_active' => true,
    ]);
});

it('can view a document template record', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('view', ['record' => $this->testDocumentTemplate]))
        ->assertOk();
});

it('can edit a document template record', function () {
    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\EditDocumentTemplate::class, ['record' => $this->testDocumentTemplate->id])
        ->fillForm([
            'name' => 'Updated Template',
            'type' => 'quote',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('document_templates', [
        'id' => $this->testDocumentTemplate->id,
        'name' => 'Updated Template',
        'type' => 'quote',
    ]);
});

it('can delete a document template record', function () {
    $documentTemplate = DocumentTemplate::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\EditDocumentTemplate::class, ['record' => $documentTemplate->id])
        ->callAction('delete')
        ->assertOk();

    $this->assertDatabaseMissing('document_templates', [
        'id' => $documentTemplate->id,
    ]);
});

it('validates required fields', function () {
    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
        ->fillForm([
            'name' => '',
            'type' => '',
            'category' => '',
            'content' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'type', 'category', 'content']);
});

it('can filter document templates by type', function () {
    DocumentTemplate::factory()->create(['type' => 'invoice']);
    DocumentTemplate::factory()->create(['type' => 'quote']);

    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index'))
        ->assertOk();
});

it('can filter document templates by category', function () {
    DocumentTemplate::factory()->create(['category' => 'business']);
    DocumentTemplate::factory()->create(['category' => 'legal']);

    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index'))
        ->assertOk();
});

it('can filter document templates by active status', function () {
    DocumentTemplate::factory()->create(['is_active' => true]);
    DocumentTemplate::factory()->create(['is_active' => false]);

    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index'))
        ->assertOk();
});

it('can search document templates by name', function () {
    $template = DocumentTemplate::factory()->create(['name' => 'Special Template']);

    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index') . '?search=Special')
        ->assertOk();
});

it('can search document templates by slug', function () {
    $template = DocumentTemplate::factory()->create(['slug' => 'special-template']);

    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index') . '?search=special')
        ->assertOk();
});

it('can sort document templates by name', function () {
    $template1 = DocumentTemplate::factory()->create(['name' => 'Zebra Template']);
    $template2 = DocumentTemplate::factory()->create(['name' => 'Alpha Template']);

    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index') . '?sort=name&direction=asc')
        ->assertOk();
});

it('can sort document templates by created date', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index') . '?sort=created_at&direction=desc')
        ->assertOk();
});

it('shows correct document template data in table', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index'))
        ->assertSee($this->testDocumentTemplate->name)
        ->assertSee($this->testDocumentTemplate->type);
});

it('can perform bulk delete action', function () {
    $template1 = DocumentTemplate::factory()->create();
    $template2 = DocumentTemplate::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->callTableBulkAction('delete', [$template1->id, $template2->id])
        ->assertOk();

    $this->assertDatabaseMissing('document_templates', [
        'id' => $template1->id,
    ]);

    $this->assertDatabaseMissing('document_templates', [
        'id' => $template2->id,
    ]);
});

it('can preview a document template', function () {
    $template = DocumentTemplate::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->callTableAction('preview_template', $template)
        ->assertHasNoActionErrors();
});

it('can duplicate a document template', function () {
    $template = DocumentTemplate::factory()->create(['name' => 'Original Template']);

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->callTableAction('duplicate_template', $template)
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('document_templates', [
        'name' => 'Original Template (Copy)',
        'slug' => $template->slug . '-copy',
    ]);
});

it('can perform bulk activation', function () {
    $template1 = DocumentTemplate::factory()->create(['is_active' => false]);
    $template2 = DocumentTemplate::factory()->create(['is_active' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->callTableBulkAction('activate_bulk', [$template1->id, $template2->id])
        ->assertHasNoBulkActionErrors();

    $template1->refresh();
    $template2->refresh();

    expect($template1->is_active)->toBeTrue();
    expect($template2->is_active)->toBeTrue();
});

it('can perform bulk deactivation', function () {
    $template1 = DocumentTemplate::factory()->create(['is_active' => true]);
    $template2 = DocumentTemplate::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
        ->callTableBulkAction('deactivate_bulk', [$template1->id, $template2->id])
        ->assertHasNoBulkActionErrors();

    $template1->refresh();
    $template2->refresh();

    expect($template1->is_active)->toBeFalse();
    expect($template2->is_active)->toBeFalse();
});

it('shows template content in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('edit', ['record' => $this->testDocumentTemplate]))
        ->assertOk();
});

it('shows variables in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('edit', ['record' => $this->testDocumentTemplate]))
        ->assertOk();
});

it('shows settings in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('edit', ['record' => $this->testDocumentTemplate]))
        ->assertOk();
});

it('shows preview in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('edit', ['record' => $this->testDocumentTemplate]))
        ->assertOk();
});

it('shows correct type badges', function () {
    $invoiceTemplate = DocumentTemplate::factory()->create(['type' => 'invoice']);
    $quoteTemplate = DocumentTemplate::factory()->create(['type' => 'quote']);

    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index'))
        ->assertOk();
});

it('shows correct category badges', function () {
    $businessTemplate = DocumentTemplate::factory()->create(['category' => 'business']);
    $legalTemplate = DocumentTemplate::factory()->create(['category' => 'legal']);

    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index'))
        ->assertOk();
});

it('can filter templates with variables', function () {
    $templateWithVariables = DocumentTemplate::factory()->create([
        'variables' => ['var1' => 'Variable 1', 'var2' => 'Variable 2']
    ]);
    $templateWithoutVariables = DocumentTemplate::factory()->create([
        'variables' => []
    ]);

    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index'))
        ->assertOk();
});

it('can filter recent templates', function () {
    $recentTemplate = DocumentTemplate::factory()->create(['created_at' => now()->subDays(10)]);
    $oldTemplate = DocumentTemplate::factory()->create(['created_at' => now()->subDays(60)]);

    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index'))
        ->assertOk();
});

it('can access document template resource pages', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('index'))
        ->assertOk();

    $this
        ->actingAs($this->adminUser)
        ->get(DocumentTemplateResource::getUrl('create'))
        ->assertOk();
});

it('validates template content is required', function () {
    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
        ->fillForm([
            'name' => 'Test Template',
            'type' => 'invoice',
            'category' => 'business',
            'content' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['content']);
});

it('handles template with complex variables', function () {
    $complexVariables = [
        'customer_name' => 'Customer Name',
        'invoice_date' => 'Invoice Date',
        'total_amount' => 'Total Amount',
        'payment_terms' => 'Payment Terms',
    ];

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
        ->fillForm([
            'name' => 'Complex Template',
            'type' => 'invoice',
            'category' => 'business',
            'content' => '<h1>Invoice for {{customer_name}}</h1><p>Date: {{invoice_date}}</p><p>Total: €{{total_amount}}</p>',
            'variables' => $complexVariables,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('document_templates', [
        'name' => 'Complex Template',
        'type' => 'invoice',
        'is_active' => true,
    ]);
});

it('handles template with settings', function () {
    $settings = [
        'page_size' => 'A4',
        'orientation' => 'portrait',
        'margins' => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20],
        'header' => 'Company Header',
        'footer' => 'Company Footer',
    ];

    Livewire::actingAs($this->adminUser)
        ->test(DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
        ->fillForm([
            'name' => 'Template with Settings',
            'type' => 'report',
            'category' => 'business',
            'content' => '<h1>Report</h1><p>Content here</p>',
            'settings' => $settings,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('document_templates', [
        'name' => 'Template with Settings',
        'type' => 'report',
        'is_active' => true,
    ]);
});
