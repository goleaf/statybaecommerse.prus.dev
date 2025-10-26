<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Contracts\DocumentServiceContract;
use App\Filament\Actions\DiscountCodeDocumentAction;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use LogicException;
use Tests\TestCase;

final class DiscountCodeDocumentActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Discount $discount;

    private DiscountCode $discountCode;

    private DocumentTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->discount = Discount::factory()->create([
            'name'  => 'Test Discount',
            'value' => 10.0,
            'type'  => 'percentage',
        ]);

        $this->discountCode = DiscountCode::factory()->create([
            'discount_id'    => $this->discount->id,
            'code'           => 'TEST10',
            'description_lt' => 'Test discount code',
            'description_en' => 'Test discount code',
            'usage_limit'    => 100,
            'usage_count'    => 0,
            'is_active'      => true,
            'status'         => 'active',
        ]);

        $this->template = DocumentTemplate::factory()->active()->create([
            'name'    => 'Discount Code Template',
            'content' => 'Discount Code: {{DISCOUNT_CODE}} - Value: {{DISCOUNT_VALUE}}',
        ]);
    }

    public function test_can_create_discount_code_document_action(): void
    {
        $action = DiscountCodeDocumentAction::make();

        expect($action)
            ->toBeInstanceOf(Action::class)
            ->and($action->getName())
            ->toBe('generate_document');
    }

    public function test_action_has_correct_properties(): void
    {
        $action = DiscountCodeDocumentAction::make();

        expect($action->getLabel())
            ->toBe(__('admin.actions.generate_document'))
            ->and($action->getIcon())
            ->toBe('heroicon-m-document-text')
            ->and($action->getColor())
            ->toBe('info');
    }

    public function test_action_form_has_required_fields(): void
    {
        $action = DiscountCodeDocumentAction::make();
        $schema = $action->getSchema(Schema::make());

        expect($schema)
            ->not->toBeNull()
            ->and($schema?->getComponents())
            ->toHaveCount(3);
    }

    public function test_generates_html_document_with_expected_variables(): void
    {
        $action = DiscountCodeDocumentAction::make();
        $handler = $action->getActionFunction();

        $this->assertNotNull($handler);

        $data = [
            'template_id' => $this->template->id,
            'format'      => 'html',
            'title'       => 'Test Document',
        ];

        $document = Document::make([
            'title'   => $data['title'],
            'content' => '<p>Generated</p>',
        ]);

        $service = $this->makeDocumentServiceFake(
            $document,
            function (DocumentTemplate $template, DiscountCode $code, array $variables, ?string $title) use ($data): void {
                expect($template->is($this->template))->toBeTrue();
                expect($code->is($this->discountCode))->toBeTrue();
                expect($variables['DISCOUNT_CODE'])->toBe($this->discountCode->code);
                expect($variables['DISCOUNT_NAME'])->toBe($this->discount->name);
                expect($variables['DISCOUNT_VALUE'])->toBe($this->discount->value);
                expect($title)->toBe($data['title']);
            }
        );

        $response = $handler($this->discountCode, $data, $service);

        expect($response)
            ->toBeInstanceOf(Response::class)
            ->and($response->getContent())
            ->toBe($document->content);
    }

    public function test_generates_pdf_redirect_when_requested(): void
    {
        $action = DiscountCodeDocumentAction::make();
        $handler = $action->getActionFunction();

        $this->assertNotNull($handler);

        $data = [
            'template_id' => $this->template->id,
            'format'      => 'pdf',
            'title'       => 'Test PDF Document',
        ];

        $document = Document::make([
            'title'   => $data['title'],
            'content' => '<p>Generated</p>',
        ]);

        $service = $this->makeDocumentServiceFake(
            $document,
            function (): void {
                // Invocation indicates success.
            },
            function (Document $document): string {
                return 'https://example.test/discount.pdf';
            }
        );

        $response = $handler($this->discountCode, $data, $service);

        expect($response)
            ->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())
            ->toBe('https://example.test/discount.pdf');
    }

    public function test_throws_exception_when_template_is_missing(): void
    {
        $action = DiscountCodeDocumentAction::make();
        $handler = $action->getActionFunction();

        $this->assertNotNull($handler);

        $data = [
            'template_id' => 999999,
            'format'      => 'html',
            'title'       => 'Missing Template',
        ];

        $service = $this->makeDocumentServiceFake(
            Document::make(),
            function (): void {
                // Should not be invoked due to missing template.
            }
        );

        expect(fn () => $handler($this->discountCode, $data, $service))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    }

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
                ($this->assertion)($template, $relatedModel, $variables, $title);

                return $this->document;
            }

            public function generatePdf(Document $document): string
            {
                if ($this->pdfHandler === null) {
                    throw new LogicException('generatePdf should not be called.');
                }

                return ($this->pdfHandler)($document);
            }
        };
    }
}
