<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class DocumentResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private DocumentTemplate $template;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User',
        ]);

        $this->template = DocumentTemplate::factory()->create([
            'name' => 'Test Template',
            'type' => 'invoice',
            'is_active' => true,
        ]);

        $this->order = Order::factory()->create([
            'number' => 'ORD-001',
        ]);
    }

    public function test_can_list_documents(): void
    {
        Document::factory()->count(3)->create([
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->assertCanSeeTableRecords(Document::all());
    }

    public function test_can_create_document(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\CreateDocument::class)
            ->fillForm([
                'title' => 'Test Document',
                'content' => 'Test content',
                'status' => 'draft',
                'format' => 'pdf',
                'document_template_id' => $this->template->id,
                'documentable_type' => Order::class,
                'documentable_id' => $this->order->id,
                'created_by' => $this->adminUser->id,
                'variables' => [
                    'company_name' => 'Test Company',
                    'order_number' => 'ORD-001',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('documents', [
            'title' => 'Test Document',
            'status' => 'draft',
            'format' => 'pdf',
        ]);
    }

    public function test_can_edit_document(): void
    {
        $document = Document::factory()->create([
            'title' => 'Original Title',
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\EditDocument::class, [
            'record' => $document->getRouteKey(),
        ])
            ->fillForm([
                'title' => 'Updated Title',
                'status' => 'generated',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Updated Title',
            'status' => 'generated',
        ]);
    }

    public function test_can_view_document(): void
    {
        $document = Document::factory()->create([
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ViewDocument::class, [
            'record' => $document->getRouteKey(),
        ])
            ->assertCanSeeTableRecords([$document]);
    }

    public function test_can_delete_document(): void
    {
        $document = Document::factory()->create([
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableAction('delete', $document);

        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
        ]);
    }

    public function test_can_generate_document(): void
    {
        $document = Document::factory()->create([
            'status' => 'draft',
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableAction('generate', $document);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'generated',
        ]);
    }

    public function test_can_publish_document(): void
    {
        $document = Document::factory()->create([
            'status' => 'generated',
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableAction('publish', $document);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'published',
        ]);
    }

    public function test_can_archive_document(): void
    {
        $document = Document::factory()->create([
            'status' => 'published',
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableAction('archive', $document);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'archived',
        ]);
    }

    public function test_can_filter_documents_by_status(): void
    {
        Document::factory()->create([
            'status' => 'draft',
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        Document::factory()->create([
            'status' => 'generated',
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->filterTable('status', 'draft')
            ->assertCanSeeTableRecords(Document::where('status', 'draft')->get())
            ->assertCanNotSeeTableRecords(Document::where('status', 'generated')->get());
    }

    public function test_can_filter_documents_by_format(): void
    {
        Document::factory()->create([
            'format' => 'pdf',
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        Document::factory()->create([
            'format' => 'html',
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->filterTable('format', 'pdf')
            ->assertCanSeeTableRecords(Document::where('format', 'pdf')->get())
            ->assertCanNotSeeTableRecords(Document::where('format', 'html')->get());
    }

    public function test_can_bulk_generate_documents(): void
    {
        $documents = Document::factory()->count(3)->create([
            'status' => 'draft',
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableBulkAction('generate', $documents);

        foreach ($documents as $document) {
            $this->assertDatabaseHas('documents', [
                'id' => $document->id,
                'status' => 'generated',
            ]);
        }
    }

    public function test_can_bulk_publish_documents(): void
    {
        $documents = Document::factory()->count(3)->create([
            'status' => 'generated',
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableBulkAction('publish', $documents);

        foreach ($documents as $document) {
            $this->assertDatabaseHas('documents', [
                'id' => $document->id,
                'status' => 'published',
            ]);
        }
    }

    public function test_can_bulk_archive_documents(): void
    {
        $documents = Document::factory()->count(3)->create([
            'status' => 'published',
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableBulkAction('archive', $documents);

        foreach ($documents as $document) {
            $this->assertDatabaseHas('documents', [
                'id' => $document->id,
                'status' => 'archived',
            ]);
        }
    }

    public function test_document_relationships_work_correctly(): void
    {
        $document = Document::factory()->create([
            'created_by' => $this->adminUser->id,
            'document_template_id' => $this->template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ]);

        $this->assertInstanceOf(DocumentTemplate::class, $document->template);
        $this->assertInstanceOf(User::class, $document->creator);
        $this->assertInstanceOf(Order::class, $document->documentable);
    }

    public function test_document_model_methods_work_correctly(): void
    {
        $document = Document::factory()->create([
            'status' => 'draft',
            'format' => 'pdf',
            'file_path' => 'documents/test.pdf',
        ]);

        $this->assertTrue($document->isDraft());
        $this->assertFalse($document->isGenerated());
        $this->assertFalse($document->isPublished());
        $this->assertTrue($document->isPdf());
        $this->assertNotNull($document->getFileUrl());
    }
}
