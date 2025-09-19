<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DocumentGenerationComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    private DocumentService $documentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->documentService = app(DocumentService::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_document_template(): void
    {
        $template = DocumentTemplate::factory()->create([
            'name' => 'Invoice Template',
            'type' => 'invoice',
            'category' => 'sales',
            'content' => '<h1>Invoice #$ORDER_NUMBER</h1><p>Customer: $CUSTOMER_NAME</p>',
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME'],
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('document_templates', [
            'name' => 'Invoice Template',
            'type' => 'invoice',
            'category' => 'sales',
            'is_active' => true,
        ]);

        $this->assertEquals(['ORDER_NUMBER', 'CUSTOMER_NAME'], $template->variables);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_generate_document_from_template(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $order = Order::factory()->create([
            'number' => 'ORD-001',
            'user_id' => $user->id,
        ]);

        $template = DocumentTemplate::factory()->create([
            'name' => 'Invoice Template',
            'type' => 'invoice',
            'content' => '<h1>Invoice #$ORDER_NUMBER</h1><p>Customer: $CUSTOMER_NAME</p>',
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME'],
        ]);

        $document = $this->documentService->generateDocument($template, $order, [
            'ORDER_NUMBER' => $order->number,
            'CUSTOMER_NAME' => $user->name,
        ]);

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals($template->id, $document->document_template_id);
        $this->assertEquals($order->id, $document->documentable_id);
        $this->assertEquals(Order::class, $document->documentable_type);
        $this->assertStringContainsString('ORD-001', $document->content);
        $this->assertStringContainsString('John Doe', $document->content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_extract_variables_from_order(): void
    {
        $user = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        $order = Order::factory()->create([
            'number' => 'ORD-002',
            'total' => 150.0,
            'user_id' => $user->id,
        ]);

        $variables = $this->documentService->extractVariablesFromModel($order);

        $this->assertIsArray($variables);
        $this->assertArrayHasKey('$ORDER_NUMBER', $variables);
        $this->assertArrayHasKey('$ORDER_TOTAL', $variables);
        $this->assertArrayHasKey('$CUSTOMER_NAME', $variables);
        $this->assertArrayHasKey('$CUSTOMER_EMAIL', $variables);

        $this->assertEquals('ORD-002', $variables['$ORDER_NUMBER']);
        $this->assertEquals('150.00', $variables['$ORDER_TOTAL']);
        $this->assertEquals('Jane Smith', $variables['$CUSTOMER_NAME']);
        $this->assertEquals('jane@example.com', $variables['$CUSTOMER_EMAIL']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_render_template_with_variables(): void
    {
        $template = DocumentTemplate::factory()->create([
            'content' => '<h1>Order $ORDER_NUMBER</h1><p>Total: €$ORDER_TOTAL</p><p>Customer: $CUSTOMER_NAME</p>',
        ]);

        $variables = [
            '$ORDER_NUMBER' => 'ORD-003',
            '$ORDER_TOTAL' => '299.99',
            '$CUSTOMER_NAME' => 'Bob Johnson',
        ];

        $renderedContent = $this->documentService->renderTemplate($template, $variables);

        $this->assertStringContainsString('Order ORD-003', $renderedContent);
        $this->assertStringContainsString('Total: €299.99', $renderedContent);
        $this->assertStringContainsString('Customer: Bob Johnson', $renderedContent);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_generate_pdf_document(): void
    {
        $template = DocumentTemplate::factory()->create([
            'content' => '<h1>PDF Test Document</h1><p>This is a test PDF.</p>',
        ]);

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $document = $this->documentService->generateDocument($template, $order, []);

        // Generate PDF from the document
        $pdfUrl = $this->documentService->generatePdf($document);

        $document->refresh();
        $this->assertEquals('pdf', $document->format);
        $this->assertNotNull($document->file_path);
        $this->assertStringContainsString('.pdf', $document->file_path);
        $this->assertNotNull($pdfUrl);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_generate_html_document(): void
    {
        $template = DocumentTemplate::factory()->create([
            'content' => '<h1>HTML Test Document</h1><p>This is a test HTML document.</p>',
        ]);

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $document = $this->documentService->generateDocument($template, $order, []);

        $this->assertEquals('html', $document->format);
        $this->assertStringContainsString('<h1>HTML Test Document</h1>', $document->content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_templates_by_type(): void
    {
        DocumentTemplate::factory()->create(['type' => 'invoice']);
        DocumentTemplate::factory()->create(['type' => 'receipt']);
        DocumentTemplate::factory()->create(['type' => 'invoice']);

        $invoiceTemplates = DocumentTemplate::ofType('invoice')->get();
        $receiptTemplates = DocumentTemplate::ofType('receipt')->get();

        $this->assertCount(2, $invoiceTemplates);
        $this->assertCount(1, $receiptTemplates);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_templates_by_category(): void
    {
        DocumentTemplate::factory()->create(['category' => 'sales']);
        DocumentTemplate::factory()->create(['category' => 'marketing']);
        DocumentTemplate::factory()->create(['category' => 'sales']);

        $salesTemplates = DocumentTemplate::ofCategory('sales')->get();
        $marketingTemplates = DocumentTemplate::ofCategory('marketing')->get();

        $this->assertCount(2, $salesTemplates);
        $this->assertCount(1, $marketingTemplates);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_active_templates(): void
    {
        DocumentTemplate::factory()->create(['is_active' => true]);
        DocumentTemplate::factory()->create(['is_active' => false]);
        DocumentTemplate::factory()->create(['is_active' => true]);

        $activeTemplates = DocumentTemplate::active()->get();

        $this->assertCount(2, $activeTemplates);

        foreach ($activeTemplates as $template) {
            $this->assertTrue($template->is_active);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_available_variables_from_template(): void
    {
        $template = DocumentTemplate::factory()->create([
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME', 'ORDER_TOTAL'],
        ]);

        $availableVariables = $template->getAvailableVariables();

        $this->assertIsArray($availableVariables);
        $this->assertCount(3, $availableVariables);
        $this->assertContains('ORDER_NUMBER', $availableVariables);
        $this->assertContains('CUSTOMER_NAME', $availableVariables);
        $this->assertContains('ORDER_TOTAL', $availableVariables);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_check_if_template_has_variable(): void
    {
        $template = DocumentTemplate::factory()->create([
            'variables' => ['ORDER_NUMBER', 'CUSTOMER_NAME'],
        ]);

        $this->assertTrue($template->hasVariable('ORDER_NUMBER'));
        $this->assertTrue($template->hasVariable('CUSTOMER_NAME'));
        $this->assertFalse($template->hasVariable('ORDER_TOTAL'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_template_settings(): void
    {
        $template = DocumentTemplate::factory()->create([
            'settings' => [
                'page_size' => 'A4',
                'orientation' => 'portrait',
                'margins' => '10mm',
            ],
        ]);

        $settings = $template->getSettings();

        $this->assertIsArray($settings);
        $this->assertEquals('A4', $settings['page_size']);
        $this->assertEquals('portrait', $settings['orientation']);
        $this->assertEquals('10mm', $settings['margins']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_specific_setting_with_default(): void
    {
        $template = DocumentTemplate::factory()->create([
            'settings' => ['page_size' => 'A4'],
        ]);

        $pageSize = $template->getSetting('page_size', 'Letter');
        $orientation = $template->getSetting('orientation', 'portrait');

        $this->assertEquals('A4', $pageSize);
        $this->assertEquals('portrait', $orientation);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_documents_by_status(): void
    {
        Document::factory()->create(['status' => 'draft']);
        Document::factory()->create(['status' => 'published']);
        Document::factory()->create(['status' => 'draft']);

        $draftDocuments = Document::ofStatus('draft')->get();
        $publishedDocuments = Document::ofStatus('published')->get();

        $this->assertCount(2, $draftDocuments);
        $this->assertCount(1, $publishedDocuments);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_documents_by_format(): void
    {
        Document::factory()->create(['format' => 'html']);
        Document::factory()->create(['format' => 'pdf']);
        Document::factory()->create(['format' => 'html']);

        $htmlDocuments = Document::ofFormat('html')->get();
        $pdfDocuments = Document::ofFormat('pdf')->get();

        $this->assertCount(2, $htmlDocuments);
        $this->assertCount(1, $pdfDocuments);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_check_if_document_is_generated(): void
    {
        $generatedDocument = Document::factory()->create([
            'status' => 'published',
            'generated_at' => now(),
            'file_path' => 'documents/test.pdf',
        ]);

        $notGeneratedDocument = Document::factory()->create([
            'status' => 'draft',
            'generated_at' => null,
            'file_path' => null,
        ]);

        $this->assertTrue($generatedDocument->isGenerated());
        $this->assertFalse($notGeneratedDocument->isGenerated());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_file_url_when_file_exists(): void
    {
        $document = Document::factory()->create([
            'file_path' => 'documents/test.pdf',
        ]);

        $fileUrl = $document->getFileUrl();

        $this->assertNotNull($fileUrl);
        $this->assertStringContainsString('documents/test.pdf', $fileUrl);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_null_for_file_url_when_no_file(): void
    {
        $document = Document::factory()->create([
            'file_path' => null,
        ]);

        $fileUrl = $document->getFileUrl();

        $this->assertNull($fileUrl);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_multilanguage_templates(): void
    {
        $template = DocumentTemplate::factory()->create([
            'content' => '<h1>$COMPANY_NAME</h1><p>$GREETING</p>',
            'variables' => ['COMPANY_NAME', 'GREETING'],
        ]);

        // Test Lithuanian variables
        app()->setLocale('lt');
        $ltVariables = [
            'COMPANY_NAME' => 'Mano Įmonė',
            'GREETING' => 'Sveiki',
        ];

        $ltContent = $this->documentService->renderTemplate($template, $ltVariables);
        $this->assertStringContainsString('Mano Įmonė', $ltContent);
        $this->assertStringContainsString('Sveiki', $ltContent);

        // Test English variables
        app()->setLocale('en');
        $enVariables = [
            'COMPANY_NAME' => 'My Company',
            'GREETING' => 'Hello',
        ];

        $enContent = $this->documentService->renderTemplate($template, $enVariables);
        $this->assertStringContainsString('My Company', $enContent);
        $this->assertStringContainsString('Hello', $enContent);
    }
}
