<?php

declare(strict_types=1);

use App\Filament\Resources\DocumentTemplateResource;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

describe('DocumentTemplate Resource', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($this->admin);
    });

    it('can render index page', function () {
        $this
            ->get(DocumentTemplateResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can list document templates', function () {
        $templates = DocumentTemplate::factory()->count(10)->create();

        livewire(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->assertCanSeeTableRecords($templates);
    });

    it('can render create page', function () {
        $this
            ->get(DocumentTemplateResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can create document template', function () {
        $newData = [
            'name' => 'New Invoice Template',
            'description' => 'A template for invoices',
            'content' => '<h1>Invoice #{{ORDER_NUMBER}}</h1>',
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME'],
            'type' => 'invoice',
            'category' => 'sales',
            'settings' => ['page_size' => 'A4'],
            'is_active' => true,
        ];

        livewire(DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('document_templates', [
            'name' => 'New Invoice Template',
            'type' => 'invoice',
            'category' => 'sales',
            'is_active' => true,
        ]);
    });

    it('validates required fields when creating', function () {
        livewire(DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
            ->fillForm([
                'name' => '',
                'content' => '',
                'type' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'content', 'type']);
    });

    it('automatically generates slug from name', function () {
        $newData = [
            'name' => 'My Custom Template',
            'content' => '<h1>Test</h1>',
            'type' => 'receipt',
            'category' => 'sales',
        ];

        livewire(DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('document_templates', [
            'name' => 'My Custom Template',
            'slug' => 'my-custom-template',
        ]);
    });

    it('can render view page', function () {
        $template = DocumentTemplate::factory()->create();

        $this
            ->get(DocumentTemplateResource::getUrl('view', ['record' => $template]))
            ->assertSuccessful();
    });

    it('can view document template', function () {
        $template = DocumentTemplate::factory()->create();

        livewire(DocumentTemplateResource\Pages\ViewDocumentTemplate::class, ['record' => $template->getRouteKey()])
            ->assertFormSet([
                'name' => $template->name,
                'type' => $template->type,
                'category' => $template->category,
                'is_active' => $template->is_active,
            ]);
    });

    it('can render edit page', function () {
        $template = DocumentTemplate::factory()->create();

        $this
            ->get(DocumentTemplateResource::getUrl('edit', ['record' => $template]))
            ->assertSuccessful();
    });

    it('can retrieve data for editing', function () {
        $template = DocumentTemplate::factory()->create();

        livewire(DocumentTemplateResource\Pages\EditDocumentTemplate::class, ['record' => $template->getRouteKey()])
            ->assertFormSet([
                'name' => $template->name,
                'description' => $template->description,
                'content' => $template->content,
                'type' => $template->type,
                'category' => $template->category,
                'is_active' => $template->is_active,
            ]);
    });

    it('can save document template', function () {
        $template = DocumentTemplate::factory()->create();
        $newData = [
            'name' => 'Updated Template',
            'description' => 'Updated description',
            'is_active' => false,
        ];

        livewire(DocumentTemplateResource\Pages\EditDocumentTemplate::class, ['record' => $template->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($template->fresh())
            ->name
            ->toBe('Updated Template')
            ->description
            ->toBe('Updated description')
            ->is_active
            ->toBeFalse();
    });

    it('can delete document template', function () {
        $template = DocumentTemplate::factory()->create();

        livewire(DocumentTemplateResource\Pages\EditDocumentTemplate::class, ['record' => $template->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($template);
    });

    it('cannot delete template with associated documents', function () {
        $template = DocumentTemplate::factory()->create();
        Document::factory()->create(['document_template_id' => $template->id]);

        livewire(DocumentTemplateResource\Pages\EditDocumentTemplate::class, ['record' => $template->getRouteKey()])
            ->callAction(DeleteAction::class)
            ->assertNotified();

        $this->assertModelExists($template);
    });

    it('can search templates by name', function () {
        $templates = DocumentTemplate::factory()->count(10)->create();
        $firstTemplate = $templates->first();

        livewire(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->searchTable($firstTemplate->name)
            ->assertCanSeeTableRecords([$firstTemplate])
            ->assertCanNotSeeTableRecords($templates->skip(1));
    });

    it('can filter templates by type', function () {
        $invoiceTemplates = DocumentTemplate::factory()->count(3)->create(['type' => 'invoice']);
        $receiptTemplates = DocumentTemplate::factory()->count(2)->create(['type' => 'receipt']);

        livewire(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->filterTable('type', 'invoice')
            ->assertCanSeeTableRecords($invoiceTemplates)
            ->assertCanNotSeeTableRecords($receiptTemplates);
    });

    it('can filter templates by category', function () {
        $salesTemplates = DocumentTemplate::factory()->count(3)->create(['category' => 'sales']);
        $marketingTemplates = DocumentTemplate::factory()->count(2)->create(['category' => 'marketing']);

        livewire(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->filterTable('category', 'sales')
            ->assertCanSeeTableRecords($salesTemplates)
            ->assertCanNotSeeTableRecords($marketingTemplates);
    });

    it('can filter templates by active status', function () {
        $activeTemplates = DocumentTemplate::factory()->count(3)->create(['is_active' => true]);
        $inactiveTemplates = DocumentTemplate::factory()->count(2)->create(['is_active' => false]);

        livewire(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords($activeTemplates)
            ->assertCanNotSeeTableRecords($inactiveTemplates);
    });

    it('can sort templates by name', function () {
        $templateA = DocumentTemplate::factory()->create(['name' => 'A Template']);
        $templateZ = DocumentTemplate::factory()->create(['name' => 'Z Template']);

        livewire(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->sortTable('name', 'asc')
            ->assertCanSeeTableRecords([$templateA, $templateZ], inOrder: true);
    });

    it('can bulk delete templates', function () {
        $templates = DocumentTemplate::factory()->count(3)->create();

        livewire(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->callTableBulkAction('delete', $templates);

        foreach ($templates as $template) {
            $this->assertModelMissing($template);
        }
    });

    it('can bulk activate templates', function () {
        $templates = DocumentTemplate::factory()->count(3)->create(['is_active' => false]);

        livewire(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->callTableBulkAction('activate', $templates);

        foreach ($templates as $template) {
            expect($template->fresh()->is_active)->toBeTrue();
        }
    });

    it('can bulk deactivate templates', function () {
        $templates = DocumentTemplate::factory()->count(3)->create(['is_active' => true]);

        livewire(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->callTableBulkAction('deactivate', $templates);

        foreach ($templates as $template) {
            expect($template->fresh()->is_active)->toBeFalse();
        }
    });

    it('can preview template', function () {
        $template = DocumentTemplate::factory()->create([
            'content' => '<h1>Hello {{CUSTOMER_NAME}}</h1>',
        ]);

        livewire(DocumentTemplateResource\Pages\ViewDocumentTemplate::class, ['record' => $template->getRouteKey()])
            ->callAction('preview')
            ->assertSee('Hello {{CUSTOMER_NAME}}');
    });

    it('can duplicate template', function () {
        $template = DocumentTemplate::factory()->create(['name' => 'Original Template']);

        livewire(DocumentTemplateResource\Pages\EditDocumentTemplate::class, ['record' => $template->getRouteKey()])
            ->callAction('duplicate');

        $this->assertDatabaseHas('document_templates', [
            'name' => 'Original Template (Copy)',
        ]);
    });

    it('shows documents count in template list', function () {
        $template = DocumentTemplate::factory()->create();
        Document::factory()->count(5)->create(['document_template_id' => $template->id]);

        livewire(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->assertTableColumnStateSet('documents_count', '5', $template);
    });

    it('restricts access to non-admin users', function () {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $this
            ->get(DocumentTemplateResource::getUrl('index'))
            ->assertForbidden();
    });

    it('validates unique slug', function () {
        DocumentTemplate::factory()->create(['slug' => 'existing-slug']);

        livewire(DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
            ->fillForm([
                'name' => 'New Template',
                'slug' => 'existing-slug',
                'content' => '<h1>Test</h1>',
                'type' => 'invoice',
                'category' => 'sales',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    });

    it('can export templates', function () {
        DocumentTemplate::factory()->count(5)->create();

        $response = livewire(DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->callAction('export');

        expect($response)->toBeInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class);
    });
});
