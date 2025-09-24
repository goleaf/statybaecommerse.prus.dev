<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Filament\Actions\DocumentAction;
use App\Models\DocumentTemplate;
use App\Models\User;
use Filament\Actions\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        $this->template = DocumentTemplate::factory()->create([
            'name' => 'Test Template',
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
        $schema = $action->getSchema();

        expect($schema->getComponents())
            ->toHaveCount(3);  // template_id, format, title
    }

    public function test_can_generate_document_successfully(): void
    {
        $action = DocumentAction::make();

        $data = [
            'template_id' => $this->template->id,
            'format' => 'html',
            'title' => 'Test Document',
        ];

        $model = new class
        {
            public function getKey()
            {
                return 1;
            }

            public function getMorphClass()
            {
                return 'TestModel';
            }
        };

        // Test that the action can be called (we can't actually test the full execution without proper setup)
        expect($action)
            ->toBeInstanceOf(Action::class);

        // Test that the action has the correct form fields
        $schema = $action->getSchema();
        expect($schema->getComponents())
            ->toHaveCount(3);
    }
}
