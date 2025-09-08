<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DocumentGenerationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected DocumentService $documentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->documentService = app(DocumentService::class);
    }

    public function test_document_template_can_be_created(): void
    {
        $template = DocumentTemplate::create([
            'name' => 'Test Invoice Template',
            'slug' => 'test-invoice',
            'type' => 'invoice',
            'category' => 'sales',
            'content' => '<h1>Invoice #$ORDER_NUMBER</h1><p>Customer: $CUSTOMER_NAME</p>',
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME'],
            'description' => 'Test invoice template',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(DocumentTemplate::class, $template);
        $this->assertEquals('Test Invoice Template', $template->name);
        $this->assertEquals('test-invoice', $template->slug);
        $this->assertEquals('invoice', $template->type);
        $this->assertEquals(['ORDER_NUMBER', 'CUSTOMER_NAME'], $template->variables);
        $this->assertTrue($template->is_active);
    }

    public function test_document_can_be_generated_from_template(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $order = Order::factory()->create([
            'number' => 'ORD-001',
            'user_id' => $user->id,
        ]);

        $template = DocumentTemplate::create([
            'name' => 'Order Invoice',
            'slug' => 'order-invoice',
            'type' => 'invoice',
            'category' => 'sales',
            'content' => '<h1>Invoice #$ORDER_NUMBER</h1><p>Customer: $CUSTOMER_NAME</p><p>Total: €$ORDER_TOTAL</p>',
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME', 'ORDER_TOTAL'],
            'is_active' => true,
        ]);

        $document = $this->documentService->generateDocument(
            $template,
            $order,
            [],
            'Order Invoice for ' . $order->number
        );

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('Order Invoice for ORD-001', $document->title);
        $this->assertEquals('html', $document->format);
        $this->assertEquals('draft', $document->status);
        $this->assertEquals($template->id, $document->document_template_id);
        $this->assertEquals($order->id, $document->documentable_id);
        $this->assertEquals(Order::class, $document->documentable_type);
    }

    public function test_document_content_has_variables_replaced(): void
    {
        $user = User::factory()->create(['name' => 'Jane Smith']);
        $order = Order::factory()->create([
            'number' => 'ORD-002',
            'user_id' => $user->id,
            'total' => 150.75,
        ]);

        $template = DocumentTemplate::create([
            'name' => 'Receipt Template',
            'slug' => 'receipt',
            'type' => 'receipt',
            'category' => 'sales',
            'content' => '<h1>Receipt #$ORDER_NUMBER</h1><p>Customer: $CUSTOMER_NAME</p><p>Total: €$ORDER_TOTAL</p><p>Date: $ORDER_DATE</p>',
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME', 'ORDER_TOTAL', 'ORDER_DATE'],
            'is_active' => true,
        ]);

        $document = $this->documentService->generateDocument($template, $order);

        $this->assertStringContainsString('Receipt #ORD-002', $document->content);
        $this->assertStringContainsString('Customer: Jane Smith', $document->content);
        $this->assertStringContainsString('Total: €150.75', $document->content);
        $this->assertStringNotContainsString('$ORDER_NUMBER', $document->content);
        $this->assertStringNotContainsString('$CUSTOMER_NAME', $document->content);
    }

    public function test_document_service_extracts_variables_from_order(): void
    {
        $user = User::factory()->create(['name' => 'Bob Johnson']);
        $order = Order::factory()->create([
            'number' => 'ORD-003',
            'user_id' => $user->id,
            'total' => 299.99,
            'status' => 'completed',
        ]);

        $variables = $this->documentService->extractVariablesFromModel($order, 'ORDER_');

        $this->assertIsArray($variables);
        $this->assertEquals('ORD-003', $variables['$ORDER_NUMBER']);
        $this->assertEquals($user->id, $variables['$ORDER_USER_ID']);
        $this->assertEquals('299.99', $variables['$ORDER_TOTAL']);
        $this->assertEquals('completed', $variables['$ORDER_STATUS']);
    }

    public function test_document_service_extracts_variables_from_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 49.99,
            'description' => 'A test product description',
        ]);

        $variables = $this->documentService->extractVariablesFromModel($product, 'PRODUCT_');

        $this->assertIsArray($variables);
        $this->assertEquals('Test Product', $variables['$PRODUCT_NAME']);
        $this->assertEquals('TEST-001', $variables['$PRODUCT_SKU']);
        $this->assertEquals('49.99', $variables['$PRODUCT_PRICE']);
    }

    public function test_document_service_has_sample_variables(): void
    {
        $variables = $this->documentService->getSampleVariables();

        $this->assertIsArray($variables);
        $this->assertArrayHasKey('$COMPANY_NAME', $variables);
        $this->assertArrayHasKey('$CURRENT_DATE', $variables);
        $this->assertArrayHasKey('$ORDER_NUMBER', $variables);
        $this->assertArrayHasKey('$CUSTOMER_NAME', $variables);
    }

    public function test_document_can_be_converted_to_pdf(): void
    {
        $user = User::factory()->create(['name' => 'PDF Test User']);
        $order = Order::factory()->create([
            'number' => 'PDF-001',
            'user_id' => $user->id,
        ]);

        $template = DocumentTemplate::create([
            'name' => 'PDF Invoice',
            'slug' => 'pdf-invoice',
            'type' => 'invoice',
            'category' => 'sales',
            'content' => '<h1>PDF Invoice #$ORDER_NUMBER</h1><p>Customer: $CUSTOMER_NAME</p>',
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME'],
            'is_active' => true,
        ]);

        $document = $this->documentService->generateDocument($template, $order);
        $pdfUrl = $this->documentService->generatePdf($document);

        $this->assertIsString($pdfUrl);
        $this->assertEquals('pdf', $document->fresh()->format);
        $this->assertNotNull($document->fresh()->file_path);
    }

    public function test_document_template_has_correct_relationships(): void
    {
        $template = DocumentTemplate::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $document = Document::create([
            'title' => 'Test Document',
            'content' => '<h1>Test</h1>',
            'format' => 'html',
            'status' => 'generated',
            'document_template_id' => $template->id,
            'documentable_id' => $order->id,
            'documentable_type' => Order::class,
        ]);

        $this->assertEquals($template->id, $document->template->id);
        $this->assertEquals($order->id, $document->documentable->id);
        $this->assertInstanceOf(Order::class, $document->documentable);
        $this->assertTrue($template->documents->contains($document));
    }

    public function test_document_template_scope_active(): void
    {
        DocumentTemplate::factory()->create(['is_active' => true]);
        DocumentTemplate::factory()->create(['is_active' => true]);
        DocumentTemplate::factory()->create(['is_active' => false]);

        $activeTemplates = DocumentTemplate::active()->get();
        $this->assertCount(2, $activeTemplates);
    }

    public function test_document_template_scope_by_type(): void
    {
        DocumentTemplate::factory()->create(['type' => 'invoice']);
        DocumentTemplate::factory()->create(['type' => 'invoice']);
        DocumentTemplate::factory()->create(['type' => 'receipt']);

        $invoiceTemplates = DocumentTemplate::ofType('invoice')->get();
        $receiptTemplates = DocumentTemplate::ofType('receipt')->get();

        $this->assertCount(2, $invoiceTemplates);
        $this->assertCount(1, $receiptTemplates);
    }

    public function test_document_template_scope_by_category(): void
    {
        DocumentTemplate::factory()->create(['category' => 'sales']);
        DocumentTemplate::factory()->create(['category' => 'sales']);
        DocumentTemplate::factory()->create(['category' => 'marketing']);

        $salesTemplates = DocumentTemplate::ofCategory('sales')->get();
        $marketingTemplates = DocumentTemplate::ofCategory('marketing')->get();

        $this->assertCount(2, $salesTemplates);
        $this->assertCount(1, $marketingTemplates);
    }

    public function test_document_service_handles_missing_variables_gracefully(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $order = Order::factory()->create([
            'number' => 'TEST-001',
            'user_id' => $user->id,
        ]);

        $template = DocumentTemplate::create([
            'name' => 'Template with Missing Vars',
            'slug' => 'missing-vars',
            'type' => 'test',
            'category' => 'test',
            'content' => '<h1>Order: $ORDER_NUMBER</h1><p>Missing: $NONEXISTENT_VAR</p>',
            'variables' => ['ORDER_NUMBER', 'NONEXISTENT_VAR'],
            'is_active' => true,
        ]);

        $document = $this->documentService->generateDocument($template, $order);

        $this->assertStringContainsString('Order: TEST-001', $document->content);
        $this->assertStringContainsString('Missing: $NONEXISTENT_VAR', $document->content); // Should remain unreplaced
    }

    public function test_document_service_supports_multilingual_templates(): void
    {
        app()->setLocale('lt');

        $user = User::factory()->create(['name' => 'Lietuvos Vartotojas']);
        $order = Order::factory()->create([
            'number' => 'LT-001',
            'user_id' => $user->id,
        ]);

        $template = DocumentTemplate::create([
            'name' => 'Lithuanian Invoice',
            'slug' => 'lithuanian-invoice',
            'type' => 'invoice',
            'category' => 'sales',
            'content' => '<h1>Sąskaita faktūra #$ORDER_NUMBER</h1><p>Klientas: $CUSTOMER_NAME</p>',
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME'],
            'is_active' => true,
        ]);

        $document = $this->documentService->generateDocument($template, $order);

        $this->assertStringContains('Sąskaita faktūra #LT-001', $document->content);
        $this->assertStringContains('Klientas: Lietuvos Vartotojas', $document->content);
    }
}
