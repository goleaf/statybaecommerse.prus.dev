<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Contracts\DocumentServiceContract;
use App\Filament\Actions\DocumentAction;
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

final class DocumentActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private DocumentTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->template = DocumentTemplate::factory()->active()->create([
            'name'    => 'Test Template',
            'content' => 'Test content with {{VARIABLE}}',
        ]);
    }

    public function test_document_action_class_exists(): void
    {
        expect(class_exists(DocumentAction::class))
            ->toBeTrue();
    }

    public function test_can_create_document_action(): void
    {
        $action = DocumentAction::make();

        expect($action)
            ->toBeInstanceOf(Action::class);
    }

    public function test_action_has_correct_properties(): void
    {
        $action = DocumentAction::make();

        expect($action->getLabel())
            ->toBe(__('admin.actions.generate_document'))
            ->and($action->getIcon())
            ->toBe('heroicon-m-document-text')
            ->and($action->getColor())
            ->toBe('info');
    }

    public function test_action_form_has_required_fields(): void
    {
        $action = DocumentAction::make();
        $schema = $action->getSchema(Schema::make());

        expect($schema)
            ->not->toBeNull()
            ->and($schema?->getComponents())
            ->toHaveCount(3);  // template_id, format, title
    }

    public function test_generates_html_document_response(): void
    {
        $action = DocumentAction::make();
        $handler = $action->getActionFunction();

        $this->assertNotNull($handler);

        $record = User::factory()->create(['name' => 'Jane Doe']);
        // Backdate timestamps to prove the action prefers real model values over runtime defaults.
        $record->forceFill([
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDay(),
        ])->saveQuietly();

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
            function (DocumentTemplate $template, User $passedRecord, array $variables, ?string $title) use ($record, $data): void {
                expect($template->is($this->template))->toBeTrue();
                expect($passedRecord->is($record))->toBeTrue();
                expect($variables['MODEL_ID'])->toBe($record->getKey());
                expect($variables['MODEL_TYPE'])->toBe($record->getMorphClass());
                expect($variables['NAME'])->toBe($record->name);
                expect($title)->toBe($data['title']);
                expect($variables['CREATED_AT'])->toBe($record->created_at?->format('d/m/Y H:i'));
                expect($variables['UPDATED_AT'])->toBe($record->updated_at?->format('d/m/Y H:i'));
            }
        );

        $response = $handler($record, $data, $service);

        expect($response)
            ->toBeInstanceOf(Response::class)
            ->and($response->getContent())
            ->toBe($document->content)
            ->and($response->headers->get('Content-Type'))
            ->toBe('text/html');
    }

    public function test_generates_pdf_document_redirect(): void
    {
        $action = DocumentAction::make();
        $handler = $action->getActionFunction();

        $this->assertNotNull($handler);

        $record = User::factory()->create();

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
                // No additional assertions beyond invocation.
            },
            function (Document $generated): string {
                return 'https://example.test/document.pdf';
            }
        );

        $response = $handler($record, $data, $service);

        expect($response)
            ->toBeInstanceOf(RedirectResponse::class)
            ->and($response->getTargetUrl())
            ->toBe('https://example.test/document.pdf');
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
