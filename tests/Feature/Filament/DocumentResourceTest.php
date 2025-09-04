<?php declare(strict_types=1);

use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

describe('Document Resource', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($this->admin);
    });

    it('can render index page', function () {
        $this
            ->get(DocumentResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can list documents', function () {
        $documents = Document::factory()->count(10)->create();

        livewire(DocumentResource\Pages\ListDocuments::class)
            ->assertCanSeeTableRecords($documents);
    });

    it('can render create page', function () {
        $this
            ->get(DocumentResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can create document', function () {
        $template = DocumentTemplate::factory()->create();
        $order = Order::factory()->create();

        $newData = [
            'document_template_id' => $template->id,
            'title' => 'Test Invoice',
            'content' => '<h1>Invoice</h1>',
            'variables' => ['order_number' => '12345'],
            'status' => 'draft',
            'format' => 'pdf',
            'documentable_type' => Order::class,
            'documentable_id' => $order->id,
        ];

        livewire(DocumentResource\Pages\CreateDocument::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('documents', [
            'title' => 'Test Invoice',
            'status' => 'draft',
            'format' => 'pdf',
        ]);
    });

    it('validates required fields when creating', function () {
        livewire(DocumentResource\Pages\CreateDocument::class)
            ->fillForm([
                'title' => '',
                'content' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['title', 'content']);
    });

    it('can render view page', function () {
        $document = Document::factory()->create();

        $this
            ->get(DocumentResource::getUrl('view', ['record' => $document]))
            ->assertSuccessful();
    });

    it('can view document', function () {
        $document = Document::factory()->create();

        livewire(DocumentResource\Pages\ViewDocument::class, ['record' => $document->getRouteKey()])
            ->assertFormSet([
                'title' => $document->title,
                'status' => $document->status,
                'format' => $document->format,
            ]);
    });

    it('can render edit page', function () {
        $document = Document::factory()->create();

        $this
            ->get(DocumentResource::getUrl('edit', ['record' => $document]))
            ->assertSuccessful();
    });

    it('can retrieve data for editing', function () {
        $document = Document::factory()->create();

        livewire(DocumentResource\Pages\EditDocument::class, ['record' => $document->getRouteKey()])
            ->assertFormSet([
                'title' => $document->title,
                'content' => $document->content,
                'status' => $document->status,
                'format' => $document->format,
            ]);
    });

    it('can save document', function () {
        $document = Document::factory()->create();
        $newData = [
            'title' => 'Updated Invoice',
            'status' => 'generated',
        ];

        livewire(DocumentResource\Pages\EditDocument::class, ['record' => $document->getRouteKey()])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($document->fresh())
            ->title
            ->toBe('Updated Invoice')
            ->status
            ->toBe('generated');
    });

    it('can delete document', function () {
        $document = Document::factory()->create();

        livewire(DocumentResource\Pages\EditDocument::class, ['record' => $document->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($document);
    });

    it('can search documents by title', function () {
        $documents = Document::factory()->count(10)->create();
        $firstDocument = $documents->first();

        livewire(DocumentResource\Pages\ListDocuments::class)
            ->searchTable($firstDocument->title)
            ->assertCanSeeTableRecords([$firstDocument])
            ->assertCanNotSeeTableRecords($documents->skip(1));
    });

    it('can filter documents by status', function () {
        $generatedDocuments = Document::factory()->count(3)->create(['status' => 'generated']);
        $draftDocuments = Document::factory()->count(2)->create(['status' => 'draft']);

        livewire(DocumentResource\Pages\ListDocuments::class)
            ->filterTable('status', 'generated')
            ->assertCanSeeTableRecords($generatedDocuments)
            ->assertCanNotSeeTableRecords($draftDocuments);
    });

    it('can filter documents by format', function () {
        $pdfDocuments = Document::factory()->count(3)->create(['format' => 'pdf']);
        $htmlDocuments = Document::factory()->count(2)->create(['format' => 'html']);

        livewire(DocumentResource\Pages\ListDocuments::class)
            ->filterTable('format', 'pdf')
            ->assertCanSeeTableRecords($pdfDocuments)
            ->assertCanNotSeeTableRecords($htmlDocuments);
    });

    it('can sort documents by created date', function () {
        $oldDocument = Document::factory()->create(['created_at' => now()->subDays(2)]);
        $newDocument = Document::factory()->create(['created_at' => now()]);

        livewire(DocumentResource\Pages\ListDocuments::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$newDocument, $oldDocument], inOrder: true);
    });

    it('can bulk delete documents', function () {
        $documents = Document::factory()->count(3)->create();

        livewire(DocumentResource\Pages\ListDocuments::class)
            ->callTableBulkAction('delete', $documents);

        foreach ($documents as $document) {
            $this->assertModelMissing($document);
        }
    });

    it('shows document template relationship', function () {
        $template = DocumentTemplate::factory()->create(['name' => 'Invoice Template']);
        $document = Document::factory()->create(['document_template_id' => $template->id]);

        livewire(DocumentResource\Pages\ViewDocument::class, ['record' => $document->getRouteKey()])
            ->assertSee('Invoice Template');
    });

    it('shows document creator relationship', function () {
        $creator = User::factory()->create(['name' => 'John Creator']);
        $document = Document::factory()->create(['created_by' => $creator->id]);

        livewire(DocumentResource\Pages\ViewDocument::class, ['record' => $document->getRouteKey()])
            ->assertSee('John Creator');
    });

    it('can generate document action', function () {
        $document = Document::factory()->create(['status' => 'draft']);

        livewire(DocumentResource\Pages\EditDocument::class, ['record' => $document->getRouteKey()])
            ->callAction('generate');

        expect($document->fresh()->status)->toBe('generated');
    });

    it('can download document action', function () {
        $document = Document::factory()->create([
            'status' => 'generated',
            'file_path' => 'documents/test.pdf',
        ]);

        $response = $this->get(DocumentResource::getUrl('download', ['record' => $document]));

        $response->assertSuccessful();
    });

    it('restricts access to non-admin users', function () {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $this
            ->get(DocumentResource::getUrl('index'))
            ->assertForbidden();
    });

    it('can view document variables in formatted way', function () {
        $document = Document::factory()->create([
            'variables' => [
                'customer_name' => 'John Doe',
                'order_number' => '12345',
                'order_total' => 100.5,
            ],
        ]);

        livewire(DocumentResource\Pages\ViewDocument::class, ['record' => $document->getRouteKey()])
            ->assertSee('John Doe')
            ->assertSee('12345')
            ->assertSee('100.5');
    });
});
