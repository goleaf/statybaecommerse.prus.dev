<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Contracts\DocumentServiceContract;
use App\Enums\DocumentTemplateType;
use App\Filament\Actions\InvoicePdfAction;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use LogicException;
use Tests\TestCase;

final class InvoicePdfActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_invoice_pdf_action(): void
    {
        $action = InvoicePdfAction::make();

        expect($action)
            ->toBeInstanceOf(Action::class)
            ->and($action->getName())
            ->toBe('generate_invoice_pdf');
    }

    public function test_generates_pdf_redirect_with_existing_invoice_template(): void
    {
        $order = Order::factory()->create();
        $template = DocumentTemplate::factory()->invoice()->active()->create([
            'name' => 'Active Invoice Template',
        ]);

        $action = InvoicePdfAction::make();
        $handler = $action->getActionFunction();
        $this->assertNotNull($handler);

        $document = Document::make([
            'title'   => 'Invoice',
            'content' => '<p>Invoice content</p>',
        ]);

        $service = $this->makeDocumentServiceFake(
            $document,
            function (DocumentTemplate $passedTemplate, Order $passedOrder) use ($template, $order): void {
                expect($passedTemplate->is($template))->toBeTrue();
                expect($passedOrder->is($order))->toBeTrue();
            },
            fn (): string => 'https://example.test/invoice.pdf'
        );

        $response = $handler($order, $service);

        expect($response)
            ->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())
            ->toBe('https://example.test/invoice.pdf');
    }

    public function test_creates_fallback_invoice_template_when_missing(): void
    {
        $order = Order::factory()->create();

        $action = InvoicePdfAction::make();
        $handler = $action->getActionFunction();
        $this->assertNotNull($handler);

        $document = Document::make([
            'title'   => 'Invoice',
            'content' => '<p>Invoice content</p>',
        ]);

        $service = $this->makeDocumentServiceFake(
            $document,
            function (DocumentTemplate $passedTemplate): void {
                expect($passedTemplate->type)->toBe(DocumentTemplateType::Invoice->value);
                expect((bool) $passedTemplate->is_active)->toBeTrue();
            },
            fn (): string => 'https://example.test/invoice.pdf'
        );

        $response = $handler($order, $service);

        expect($response)
            ->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())
            ->toBe('https://example.test/invoice.pdf');

        $this->assertDatabaseHas('document_templates', [
            'slug'      => 'invoice-template',
            'type'      => DocumentTemplateType::Invoice->value,
            'is_active' => true,
        ]);
    }

    public function test_backfills_invoice_template_on_generated_document_before_pdf_generation(): void
    {
        $order = Order::factory()->create();
        $template = DocumentTemplate::factory()->invoice()->active()->create();

        $action = InvoicePdfAction::make();
        $handler = $action->getActionFunction();
        $this->assertNotNull($handler);

        $document = Document::make([
            'title'   => 'Invoice',
            'content' => '<p>Invoice content</p>',
        ]);

        $service = $this->makeDocumentServiceFake(
            $document,
            function (DocumentTemplate $passedTemplate, Order $passedOrder) use ($template, $order): void {
                expect($passedTemplate->is($template))->toBeTrue();
                expect($passedOrder->is($order))->toBeTrue();
            },
            function (Document $passedDocument) use ($template): string {
                expect((int) ($passedDocument->document_template_id ?? 0))->toBe((int) $template->getKey());
                expect($passedDocument->template)->toBeInstanceOf(DocumentTemplate::class);
                expect($passedDocument->template?->is($template))->toBeTrue();

                return 'https://example.test/invoice.pdf';
            }
        );

        $response = $handler($order, $service);

        expect($response)
            ->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())
            ->toBe('https://example.test/invoice.pdf');
    }

    /**
     * @param callable(DocumentTemplate, Order):void $assertion
     * @param callable(Document):string|null         $pdfHandler
     */
    private function makeDocumentServiceFake(Document $document, callable $assertion, ?callable $pdfHandler = null): DocumentServiceContract
    {
        return new class($document, $assertion, $pdfHandler) implements DocumentServiceContract
        {
            /** @var callable */
            private $assertion;

            /** @var callable|null */
            private $pdfHandler;

            public function __construct(private Document $document, callable $assertion, ?callable $pdfHandler)
            {
                $this->assertion = $assertion;
                $this->pdfHandler = $pdfHandler;
            }

            public function generateDocument(DocumentTemplate $template, \Illuminate\Database\Eloquent\Model $relatedModel, array $variables = [], ?string $title = null, bool $sendNotification = false): Document
            {
                ($this->assertion)($template, $relatedModel);

                return $this->document;
            }

            public function generatePdf(Document $document): string
            {
                if ($this->pdfHandler === null) {
                    throw new LogicException('generatePdf should not be called.');
                }

                return ($this->pdfHandler)($document);
            }

            public function extractVariablesFromModel(\Illuminate\Database\Eloquent\Model $model, string $prefix = ''): array
            {
                return [];
            }

            public function getAvailableVariables(): array
            {
                return [];
            }
        };
    }
}
