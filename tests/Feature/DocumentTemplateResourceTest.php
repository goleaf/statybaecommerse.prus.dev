<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class DocumentTemplateResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
        ]));
    }

    public function test_can_list_document_templates(): void
    {
        $templates = DocumentTemplate::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->assertCanSeeTableRecords($templates);
    }

    public function test_can_create_document_template(): void
    {
        $templateData = [
            'name' => 'Test Template',
            'description' => 'Test template description',
            'content' => 'Test template content',
            'type' => 'invoice',
            'category' => 'financial',
            'is_active' => true,
        ];

        Livewire::test(\App\Filament\Resources\DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
            ->fillForm($templateData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('document_templates', [
            'name' => 'Test Template',
            'type' => 'invoice',
            'category' => 'financial',
        ]);
    }

    public function test_can_edit_document_template(): void
    {
        $template = DocumentTemplate::factory()->create();

        Livewire::test(\App\Filament\Resources\DocumentTemplateResource\Pages\EditDocumentTemplate::class, [
            'record' => $template->id,
        ])
            ->fillForm([
                'name' => 'Updated Template Name',
                'content' => 'Updated template content',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('document_templates', [
            'id' => $template->id,
            'name' => 'Updated Template Name',
            'content' => 'Updated template content',
        ]);
    }

    public function test_can_view_document_template(): void
    {
        $template = DocumentTemplate::factory()->create();

        Livewire::test(\App\Filament\Resources\DocumentTemplateResource\Pages\ViewDocumentTemplate::class, [
            'record' => $template->id,
        ])
            ->assertOk();
    }

    public function test_can_delete_document_template(): void
    {
        $template = DocumentTemplate::factory()->create();

        Livewire::test(\App\Filament\Resources\DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->callTableAction('delete', $template)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('document_templates', [
            'id' => $template->id,
        ]);
    }

    public function test_can_filter_templates_by_type(): void
    {
        $invoiceTemplate = DocumentTemplate::factory()->create(['type' => 'invoice']);
        $receiptTemplate = DocumentTemplate::factory()->create(['type' => 'receipt']);

        Livewire::test(\App\Filament\Resources\DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->filterTable('type', 'invoice')
            ->assertCanSeeTableRecords([$invoiceTemplate])
            ->assertCanNotSeeTableRecords([$receiptTemplate]);
    }

    public function test_can_filter_templates_by_category(): void
    {
        $financialTemplate = DocumentTemplate::factory()->create(['category' => 'financial']);
        $legalTemplate = DocumentTemplate::factory()->create(['category' => 'legal']);

        Livewire::test(\App\Filament\Resources\DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->filterTable('category', 'financial')
            ->assertCanSeeTableRecords([$financialTemplate])
            ->assertCanNotSeeTableRecords([$legalTemplate]);
    }

    public function test_can_filter_templates_by_active_status(): void
    {
        $activeTemplate = DocumentTemplate::factory()->create(['is_active' => true]);
        $inactiveTemplate = DocumentTemplate::factory()->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\DocumentTemplateResource\Pages\ListDocumentTemplates::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$activeTemplate])
            ->assertCanNotSeeTableRecords([$inactiveTemplate]);
    }

    public function test_template_form_validation(): void
    {
        Livewire::test(\App\Filament\Resources\DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
            ->fillForm([
                'name' => '', // Required field
                'type' => 'invalid_type', // Invalid type
                'category' => 'invalid_category', // Invalid category
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'type', 'category']);
    }

    public function test_template_slug_auto_generation(): void
    {
        Livewire::test(\App\Filament\Resources\DocumentTemplateResource\Pages\CreateDocumentTemplate::class)
            ->fillForm([
                'name' => 'Test Template Name',
                'type' => 'invoice',
                'category' => 'financial',
                'content' => 'Test content',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('document_templates', [
            'name' => 'Test Template Name',
            'slug' => 'test-template-name',
        ]);
    }

    public function test_template_has_documents_relationship(): void
    {
        $template = DocumentTemplate::factory()->create();
        $document = \App\Models\Document::factory()->create(['document_template_id' => $template->id]);

        $this->assertTrue($template->documents()->exists());
        $this->assertEquals($document->id, $template->documents()->first()->id);
    }

    public function test_template_scope_by_type(): void
    {
        $invoiceTemplate = DocumentTemplate::factory()->create(['type' => 'invoice']);
        $receiptTemplate = DocumentTemplate::factory()->create(['type' => 'receipt']);

        $invoiceTemplates = DocumentTemplate::ofType('invoice')->get();
        $this->assertCount(1, $invoiceTemplates);
        $this->assertEquals($invoiceTemplate->id, $invoiceTemplates->first()->id);
    }

    public function test_template_scope_by_category(): void
    {
        $financialTemplate = DocumentTemplate::factory()->create(['category' => 'financial']);
        $legalTemplate = DocumentTemplate::factory()->create(['category' => 'legal']);

        $financialTemplates = DocumentTemplate::ofCategory('financial')->get();
        $this->assertCount(1, $financialTemplates);
        $this->assertEquals($financialTemplate->id, $financialTemplates->first()->id);
    }

    public function test_template_scope_active(): void
    {
        $activeTemplate = DocumentTemplate::factory()->create(['is_active' => true]);
        $inactiveTemplate = DocumentTemplate::factory()->create(['is_active' => false]);

        $activeTemplates = DocumentTemplate::active()->get();
        $this->assertCount(1, $activeTemplates);
        $this->assertEquals($activeTemplate->id, $activeTemplates->first()->id);
    }

    public function test_template_render_with_variables(): void
    {
        $template = DocumentTemplate::factory()->create([
            'content' => 'Hello {{name}}, your order {{order_id}} is ready!',
        ]);

        $variables = [
            'name' => 'John Doe',
            'order_id' => '12345',
        ];

        $rendered = $template->render($variables);
        $this->assertEquals('Hello John Doe, your order 12345 is ready!', $rendered);
    }

    public function test_template_get_available_variables(): void
    {
        $template = DocumentTemplate::factory()->create([
            'variables' => ['name', 'email', 'order_id'],
        ]);

        $variables = $template->getAvailableVariables();
        $this->assertEquals(['name', 'email', 'order_id'], $variables);
    }

    public function test_template_has_variable(): void
    {
        $template = DocumentTemplate::factory()->create([
            'variables' => ['name', 'email', 'order_id'],
        ]);

        $this->assertTrue($template->hasVariable('name'));
        $this->assertTrue($template->hasVariable('email'));
        $this->assertFalse($template->hasVariable('phone'));
    }

    public function test_template_get_settings(): void
    {
        $settings = ['header' => 'Company Header', 'footer' => 'Company Footer'];
        $template = DocumentTemplate::factory()->create(['settings' => $settings]);

        $this->assertEquals($settings, $template->getSettings());
    }

    public function test_template_get_setting(): void
    {
        $settings = ['header' => 'Company Header', 'footer' => 'Company Footer'];
        $template = DocumentTemplate::factory()->create(['settings' => $settings]);

        $this->assertEquals('Company Header', $template->getSetting('header'));
        $this->assertEquals('Default Value', $template->getSetting('nonexistent', 'Default Value'));
    }

    public function test_template_get_print_settings(): void
    {
        $template = DocumentTemplate::factory()->create();

        $printSettings = $template->getPrintSettings();
        $this->assertArrayHasKey('header', $printSettings);
        $this->assertArrayHasKey('footer', $printSettings);
        $this->assertArrayHasKey('page_size', $printSettings);
        $this->assertEquals('A4', $printSettings['page_size']);
    }
}
