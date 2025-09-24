<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class DocumentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
        ]));
    }

    public function test_can_list_documents(): void
    {
        $documents = Document::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->assertCanSeeTableRecords($documents);
    }

    public function test_can_create_document(): void
    {
        $template = DocumentTemplate::factory()->create();
        $order = Order::factory()->create();
        $user = User::factory()->create();

        $documentData = [
            'title' => 'Test Document',
            'content' => 'Test content',
            'status' => 'draft',
            'format' => 'pdf',
            'document_template_id' => $template->id,
            'documentable_type' => Order::class,
            'documentable_id' => $order->id,
            'created_by' => $user->id,
        ];

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\CreateDocument::class)
            ->fillForm($documentData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('documents', [
            'title' => 'Test Document',
            'content' => 'Test content',
            'status' => 'draft',
        ]);
    }

    public function test_can_edit_document(): void
    {
        $document = Document::factory()->create();

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\EditDocument::class, [
            'record' => $document->id,
        ])
            ->fillForm([
                'title' => 'Updated Document Title',
                'content' => 'Updated content',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Updated Document Title',
            'content' => 'Updated content',
        ]);
    }

    public function test_can_view_document(): void
    {
        $document = Document::factory()->create();

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ViewDocument::class, [
            'record' => $document->id,
        ])
            ->assertOk();
    }

    public function test_can_delete_document(): void
    {
        $document = Document::factory()->create();

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableAction('delete', $document)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
        ]);
    }

    public function test_can_generate_document(): void
    {
        $document = Document::factory()->create(['status' => 'draft']);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableAction('generate', $document)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'generated',
        ]);
    }

    public function test_can_publish_document(): void
    {
        $document = Document::factory()->create(['status' => 'generated']);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableAction('publish', $document)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'published',
        ]);
    }

    public function test_can_archive_document(): void
    {
        $document = Document::factory()->create(['status' => 'published']);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableAction('archive', $document)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'archived',
        ]);
    }

    public function test_can_filter_documents_by_status(): void
    {
        $draftDocument = Document::factory()->create(['status' => 'draft']);
        $publishedDocument = Document::factory()->create(['status' => 'published']);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->filterTable('status', 'draft')
            ->assertCanSeeTableRecords([$draftDocument])
            ->assertCanNotSeeTableRecords([$publishedDocument]);
    }

    public function test_can_filter_documents_by_format(): void
    {
        $pdfDocument = Document::factory()->create(['format' => 'pdf']);
        $htmlDocument = Document::factory()->create(['format' => 'html']);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->filterTable('format', 'pdf')
            ->assertCanSeeTableRecords([$pdfDocument])
            ->assertCanNotSeeTableRecords([$htmlDocument]);
    }

    public function test_can_filter_documents_by_template(): void
    {
        $template1 = DocumentTemplate::factory()->create();
        $template2 = DocumentTemplate::factory()->create();

        $document1 = Document::factory()->create(['document_template_id' => $template1->id]);
        $document2 = Document::factory()->create(['document_template_id' => $template2->id]);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->filterTable('template', $template1->id)
            ->assertCanSeeTableRecords([$document1])
            ->assertCanNotSeeTableRecords([$document2]);
    }

    public function test_can_filter_documents_by_creator(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $document1 = Document::factory()->create(['created_by' => $user1->id]);
        $document2 = Document::factory()->create(['created_by' => $user2->id]);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->filterTable('creator', $user1->id)
            ->assertCanSeeTableRecords([$document1])
            ->assertCanNotSeeTableRecords([$document2]);
    }

    public function test_can_filter_documents_by_generated_status(): void
    {
        $generatedDocument = Document::factory()->create(['generated_at' => now()]);
        $draftDocument = Document::factory()->create(['generated_at' => null]);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->filterTable('is_generated', true)
            ->assertCanSeeTableRecords([$generatedDocument])
            ->assertCanNotSeeTableRecords([$draftDocument]);
    }

    public function test_can_filter_documents_by_file_attachment(): void
    {
        $documentWithFile = Document::factory()->create(['file_path' => 'documents/test.pdf']);
        $documentWithoutFile = Document::factory()->create(['file_path' => null]);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->filterTable('has_file', true)
            ->assertCanSeeTableRecords([$documentWithFile])
            ->assertCanNotSeeTableRecords([$documentWithoutFile]);
    }

    public function test_can_bulk_generate_documents(): void
    {
        $documents = Document::factory()->count(3)->create(['status' => 'draft']);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableBulkAction('generate', $documents)
            ->assertHasNoTableBulkActionErrors();

        foreach ($documents as $document) {
            $this->assertDatabaseHas('documents', [
                'id' => $document->id,
                'status' => 'generated',
            ]);
        }
    }

    public function test_can_bulk_publish_documents(): void
    {
        $documents = Document::factory()->count(3)->create(['status' => 'generated']);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableBulkAction('publish', $documents)
            ->assertHasNoTableBulkActionErrors();

        foreach ($documents as $document) {
            $this->assertDatabaseHas('documents', [
                'id' => $document->id,
                'status' => 'published',
            ]);
        }
    }

    public function test_can_bulk_archive_documents(): void
    {
        $documents = Document::factory()->count(3)->create(['status' => 'published']);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->callTableBulkAction('archive', $documents)
            ->assertHasNoTableBulkActionErrors();

        foreach ($documents as $document) {
            $this->assertDatabaseHas('documents', [
                'id' => $document->id,
                'status' => 'archived',
            ]);
        }
    }

    public function test_document_form_validation(): void
    {
        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\CreateDocument::class)
            ->fillForm([
                'title' => '', // Required field
                'status' => 'invalid_status', // Invalid status
                'format' => 'invalid_format', // Invalid format
            ])
            ->call('create')
            ->assertHasFormErrors(['title', 'status', 'format']);
    }

    public function test_document_groups_by_status(): void
    {
        $draftDocument = Document::factory()->create(['status' => 'draft']);
        $publishedDocument = Document::factory()->create(['status' => 'published']);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->assertCanSeeTableRecords([$draftDocument, $publishedDocument]);
    }

    public function test_document_groups_by_format(): void
    {
        $pdfDocument = Document::factory()->create(['format' => 'pdf']);
        $htmlDocument = Document::factory()->create(['format' => 'html']);

        Livewire::test(\App\Filament\Resources\DocumentResource\Pages\ListDocuments::class)
            ->assertCanSeeTableRecords([$pdfDocument, $htmlDocument]);
    }
}
