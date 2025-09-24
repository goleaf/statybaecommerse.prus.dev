<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Filament\Actions\DiscountCodeDocumentAction;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DocumentTemplate;
use App\Models\User;
use App\Services\DocumentService;
use Filament\Actions\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

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
            'name' => 'Test Discount',
            'value' => 10.0,
            'type' => 'percentage',
        ]);

        $this->discountCode = DiscountCode::factory()->create([
            'discount_id' => $this->discount->id,
            'code' => 'TEST10',
            'description_lt' => 'Test discount code',
            'description_en' => 'Test discount code',
            'usage_limit' => 100,
            'usage_count' => 0,
            'is_active' => true,
            'status' => 'active',
        ]);

        $this->template = DocumentTemplate::factory()->create([
            'name' => 'Discount Code Template',
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
        $form = $action->getForm();

        expect($form->getComponents())
            ->toHaveCount(3);
    }

    public function test_can_generate_document_successfully(): void
    {
        Storage::fake('local');

        $action = DiscountCodeDocumentAction::make();

        $data = [
            'template_id' => $this->template->id,
            'format' => 'html',
            'title' => 'Test Document',
        ];

        $action->action($this->discountCode, $data, app(DocumentService::class));

        $this->assertDatabaseHas('documents', [
            'document_template_id' => $this->template->id,
            'title' => 'Test Document',
            'documentable_type' => DiscountCode::class,
            'documentable_id' => $this->discountCode->id,
        ]);
    }

    public function test_generates_pdf_download_when_format_is_pdf(): void
    {
        Storage::fake('local');

        $action = DiscountCodeDocumentAction::make();

        $data = [
            'template_id' => $this->template->id,
            'format' => 'pdf',
            'title' => 'Test PDF Document',
        ];

        $response = $action->action($this->discountCode, $data, app(DocumentService::class));

        expect($response)
            ->toBeInstanceOf(\Illuminate\Http\Response::class);
    }

    public function test_handles_document_generation_errors_gracefully(): void
    {
        $action = DiscountCodeDocumentAction::make();

        // Test with invalid template ID
        $data = [
            'template_id' => 99999,  // Non-existent template
            'format' => 'html',
            'title' => 'Test Document',
        ];

        $this->expectException(\Exception::class);

        $action->action($this->discountCode, $data, app(DocumentService::class));
    }

    public function test_action_variables_are_correctly_passed(): void
    {
        Storage::fake('local');

        $action = DiscountCodeDocumentAction::make();

        $data = [
            'template_id' => $this->template->id,
            'format' => 'html',
            'title' => 'Test Document',
        ];

        $action->action($this->discountCode, $data, app(DocumentService::class));

        $document = \App\Models\Document::where('documentable_id', $this->discountCode->id)->first();

        expect($document->variables)
            ->toHaveKey('DISCOUNT_CODE')
            ->and($document->variables['DISCOUNT_CODE'])
            ->toBe('TEST10')
            ->and($document->variables)
            ->toHaveKey('DISCOUNT_NAME')
            ->and($document->variables['DISCOUNT_NAME'])
            ->toBe('Test Discount');
    }
}
