<?php

declare(strict_types=1);

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Document Model', function () {
    it('can be created with valid data', function () {
        $template = DocumentTemplate::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create();

        $document = Document::create([
            'document_template_id' => $template->id,
            'title' => 'Test Invoice',
            'content' => '<h1>Invoice</h1>',
            'variables' => ['order_number' => '12345'],
            'status' => 'generated',
            'format' => 'pdf',
            'file_path' => 'documents/invoice-12345.pdf',
            'documentable_type' => Order::class,
            'documentable_id' => $order->id,
            'created_by' => $user->id,
            'generated_at' => now(),
        ]);

        expect($document)
            ->toBeInstanceOf(Document::class)
            ->and($document->title)
            ->toBe('Test Invoice')
            ->and($document->variables)
            ->toBe(['order_number' => '12345'])
            ->and($document->status)
            ->toBe('generated')
            ->and($document->format)
            ->toBe('pdf');
    });

    it('has correct fillable attributes', function () {
        $document = new Document;

        expect($document->getFillable())->toBe([
            'document_template_id',
            'title',
            'content',
            'variables',
            'status',
            'format',
            'file_path',
            'documentable_type',
            'documentable_id',
            'created_by',
            'generated_at',
        ]);
    });

    it('casts variables to array', function () {
        $document = Document::factory()->create([
            'variables' => ['key' => 'value', 'number' => 123],
        ]);

        expect($document->variables)
            ->toBeArray()
            ->and($document->variables['key'])
            ->toBe('value')
            ->and($document->variables['number'])
            ->toBe(123);
    });

    it('casts generated_at to datetime', function () {
        $document = Document::factory()->create([
            'generated_at' => '2024-01-15 10:30:00',
        ]);

        expect($document->generated_at)->toBeInstanceOf(Carbon\Carbon::class);
    });

    it('belongs to a document template', function () {
        $template = DocumentTemplate::factory()->create();
        $document = Document::factory()->create([
            'document_template_id' => $template->id,
        ]);

        expect($document->template)
            ->toBeInstanceOf(DocumentTemplate::class)
            ->and($document->template->id)
            ->toBe($template->id);
    });

    it('belongs to a creator user', function () {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'created_by' => $user->id,
        ]);

        expect($document->creator)
            ->toBeInstanceOf(User::class)
            ->and($document->creator->id)
            ->toBe($user->id);
    });

    it('has polymorphic documentable relationship', function () {
        $order = Order::factory()->create();
        $document = Document::factory()->create([
            'documentable_type' => Order::class,
            'documentable_id' => $order->id,
        ]);

        expect($document->documentable)
            ->toBeInstanceOf(Order::class)
            ->and($document->documentable->id)
            ->toBe($order->id);
    });

    it('returns variables used', function () {
        $variables = ['customer_name' => 'John Doe', 'order_total' => 100.5];
        $document = Document::factory()->create([
            'variables' => $variables,
        ]);

        expect($document->getVariablesUsed())->toBe($variables);
    });

    it('returns empty array when no variables', function () {
        $document = Document::factory()->create([
            'variables' => null,
        ]);

        expect($document->getVariablesUsed())->toBe([]);
    });

    it('can check if document is generated', function () {
        $generatedDocument = Document::factory()->create(['status' => 'generated']);
        $draftDocument = Document::factory()->create(['status' => 'draft']);

        expect($generatedDocument->isGenerated())
            ->toBeTrue()
            ->and($draftDocument->isGenerated())
            ->toBeFalse();
    });

    it('can get file url when file path exists', function () {
        $document = Document::factory()->create([
            'file_path' => 'documents/test.pdf',
        ]);

        expect($document->getFileUrl())->toContain('documents/test.pdf');
    });

    it('returns null for file url when no file path', function () {
        $document = Document::factory()->create([
            'file_path' => null,
        ]);

        expect($document->getFileUrl())->toBeNull();
    });

    it('can scope by status', function () {
        Document::factory()->create(['status' => 'generated']);
        Document::factory()->create(['status' => 'draft']);
        Document::factory()->create(['status' => 'generated']);

        $generatedDocuments = Document::whereStatus('generated')->get();
        $draftDocuments = Document::whereStatus('draft')->get();

        expect($generatedDocuments)
            ->toHaveCount(2)
            ->and($draftDocuments)
            ->toHaveCount(1);
    });

    it('can scope by format', function () {
        Document::factory()->create(['format' => 'pdf']);
        Document::factory()->create(['format' => 'html']);
        Document::factory()->create(['format' => 'pdf']);

        $pdfDocuments = Document::where('format', 'pdf')->get();
        $htmlDocuments = Document::where('format', 'html')->get();

        expect($pdfDocuments)
            ->toHaveCount(2)
            ->and($htmlDocuments)
            ->toHaveCount(1);
    });

    it('validates required fields', function () {
        expect(fn () => Document::create([]))
            ->toThrow(Illuminate\Database\QueryException::class);
    });
});
