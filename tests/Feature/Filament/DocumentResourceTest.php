<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected DocumentTemplate $template;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Admin User',
        ]);

        // Ensure role exists and assign it
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('super_admin');

        // Create document template
        $this->template = DocumentTemplate::factory()->create([
            'name' => 'Invoice Template',
            'type' => 'invoice',
            'category' => 'sales',
            'content' => '<h1>Invoice #$ORDER_NUMBER</h1><p>Customer: $CUSTOMER_NAME</p>',
        ]);

        // Create an order for document association
        $this->order = Order::factory()->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_render_document_index_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(DocumentResource::getUrl('index'));

        $response->assertSuccessful();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_list_documents(): void
    {
        $this->actingAs($this->adminUser);

        $documents = Document::factory()->count(3)->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
        ]);

        Livewire::test(DocumentResource\Pages\ListDocuments::class)
            ->assertCanSeeTableRecords($documents);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_render_document_create_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(DocumentResource::getUrl('create'));

        $response->assertSuccessful();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_create_document(): void
    {
        $this->actingAs($this->adminUser);

        $newData = [
            'document_template_id' => $this->template->id,
            'title' => 'Test Document',
            'content' => '<h1>Test Content</h1>',
            'status' => 'draft',
            'format' => 'html',
            'variables' => [
                'ORDER_NUMBER' => '12345',
                'CUSTOMER_NAME' => 'John Doe',
            ],
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ];

        Livewire::test(DocumentResource\Pages\CreateDocument::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('documents', [
            'title' => 'Test Document',
            'status' => 'draft',
            'format' => 'html',
            'creator_id' => $this->adminUser->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_validate_required_fields(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(DocumentResource\Pages\CreateDocument::class)
            ->fillForm([
                'title' => '',
                'document_template_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['title', 'document_template_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_render_document_edit_page(): void
    {
        $this->actingAs($this->adminUser);

        $document = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
        ]);

        $response = $this->get(DocumentResource::getUrl('edit', [
            'record' => $document,
        ]));

        $response->assertSuccessful();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_retrieve_document_data(): void
    {
        $this->actingAs($this->adminUser);

        $document = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
            'title' => 'Original Document',
            'status' => 'draft',
        ]);

        Livewire::test(DocumentResource\Pages\EditDocument::class, [
            'record' => $document->getRouteKey(),
        ])
            ->assertFormSet([
                'title' => 'Original Document',
                'status' => 'draft',
                'document_template_id' => $this->template->id,
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_save_document(): void
    {
        $this->actingAs($this->adminUser);

        $document = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
        ]);

        $newData = [
            'title' => 'Updated Document Title',
            'content' => '<h1>Updated Content</h1>',
            'status' => 'published',
            'format' => 'pdf',
        ];

        Livewire::test(DocumentResource\Pages\EditDocument::class, [
            'record' => $document->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($document->refresh())
            ->title
            ->toBe('Updated Document Title')
            ->status
            ->toBe('published')
            ->format
            ->toBe('pdf');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_delete_document(): void
    {
        $this->actingAs($this->adminUser);

        $document = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
        ]);

        Livewire::test(DocumentResource\Pages\EditDocument::class, [
            'record' => $document->getRouteKey(),
        ])
            ->callAction(TestAction::make('delete'))
            ->assertRedirect(DocumentResource::getUrl('index'));

        $this->assertModelMissing($document);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_view_document(): void
    {
        $this->actingAs($this->adminUser);

        $document = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
            'title' => 'View Test Document',
        ]);

        $response = $this->get(DocumentResource::getUrl('view', [
            'record' => $document,
        ]));

        $response->assertSuccessful();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_filter_documents_by_template(): void
    {
        $this->actingAs($this->adminUser);

        $otherTemplate = DocumentTemplate::factory()->create([
            'name' => 'Receipt Template',
            'type' => 'receipt',
        ]);

        $invoiceDocument = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
        ]);

        $receiptDocument = Document::factory()->create([
            'document_template_id' => $otherTemplate->id,
            'creator_id' => $this->adminUser->id,
        ]);

        Livewire::test(DocumentResource\Pages\ListDocuments::class)
            ->filterTable('template', $this->template->id)
            ->assertCanSeeTableRecords([$invoiceDocument])
            ->assertCanNotSeeTableRecords([$receiptDocument]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_filter_documents_by_status(): void
    {
        $this->actingAs($this->adminUser);

        $draftDocument = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
            'status' => 'draft',
        ]);

        $publishedDocument = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
            'status' => 'published',
        ]);

        Livewire::test(DocumentResource\Pages\ListDocuments::class)
            ->filterTable('status', 'draft')
            ->assertCanSeeTableRecords([$draftDocument])
            ->assertCanNotSeeTableRecords([$publishedDocument]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_search_documents(): void
    {
        $this->actingAs($this->adminUser);

        $searchableDocument = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
            'title' => 'Unique Document Title',
        ]);

        $otherDocument = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
            'title' => 'Different Title',
        ]);

        Livewire::test(DocumentResource\Pages\ListDocuments::class)
            ->searchTable('Unique Document')
            ->assertCanSeeTableRecords([$searchableDocument])
            ->assertCanNotSeeTableRecords([$otherDocument]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_bulk_delete_documents(): void
    {
        $this->actingAs($this->adminUser);

        $documents = Document::factory()->count(3)->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
        ]);

        Livewire::test(DocumentResource\Pages\ListDocuments::class)
            ->selectTableRecords($documents)
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertCanNotSeeTableRecords($documents);

        foreach ($documents as $document) {
            $this->assertModelMissing($document);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_generate_pdf_action(): void
    {
        $this->actingAs($this->adminUser);

        $document = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
            'format' => 'html',
            'content' => '<h1>Test PDF Content</h1>',
        ]);

        Livewire::test(DocumentResource\Pages\ListDocuments::class)
            ->callAction(TestAction::make('generate_pdf')->table($document))
            ->assertHasNoActionErrors();

        // Verify the document format was updated or PDF was generated
        // This would depend on your actual PDF generation implementation
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_download_document_action(): void
    {
        $this->actingAs($this->adminUser);

        $document = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
            'format' => 'pdf',
            'file_path' => 'documents/test.pdf',
        ]);

        // Test that the download action is visible for PDF documents
        Livewire::test(DocumentResource\Pages\ListDocuments::class)
            ->assertActionVisible(TestAction::make('download')->table($document));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validates_document_variables_format(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(DocumentResource\Pages\CreateDocument::class)
            ->fillForm([
                'title' => 'Test Document',
                'document_template_id' => $this->template->id,
                'variables' => 'invalid-json-format',
            ])
            ->call('create')
            ->assertHasFormErrors(['variables']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_update_document_status(): void
    {
        $this->actingAs($this->adminUser);

        $document = Document::factory()->create([
            'document_template_id' => $this->template->id,
            'creator_id' => $this->adminUser->id,
            'status' => 'draft',
        ]);

        Livewire::test(DocumentResource\Pages\EditDocument::class, [
            'record' => $document->getRouteKey(),
        ])
            ->fillForm(['status' => 'published'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($document->refresh()->status)->toBe('published');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function can_associate_document_with_model(): void
    {
        $this->actingAs($this->adminUser);

        $newData = [
            'document_template_id' => $this->template->id,
            'title' => 'Associated Document',
            'content' => '<h1>Content</h1>',
            'status' => 'draft',
            'format' => 'html',
            'documentable_type' => Order::class,
            'documentable_id' => $this->order->id,
        ];

        Livewire::test(DocumentResource\Pages\CreateDocument::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $document = Document::where('title', 'Associated Document')->first();

        expect($document)
            ->documentable_type
            ->toBe(Order::class)
            ->documentable_id
            ->toBe($this->order->id);
    }
}
