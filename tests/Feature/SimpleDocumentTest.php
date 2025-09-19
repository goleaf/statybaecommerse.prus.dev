<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SimpleDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_document(): void
    {
        $user = User::factory()->create();
        $template = DocumentTemplate::factory()->create();

        $document = Document::create([
            'title' => 'Test Document',
            'content' => 'Test content',
            'status' => 'draft',
            'format' => 'pdf',
            'document_template_id' => $template->id,
            'documentable_type' => 'App\Models\Order',
            'documentable_id' => 1,
            'created_by' => $user->id,
            'variables' => [
                'company_name' => 'Test Company',
                'order_number' => 'ORD-001',
            ],
        ]);

        $this->assertDatabaseHas('documents', [
            'title' => 'Test Document',
            'status' => 'draft',
            'format' => 'pdf',
        ]);

        $this->assertEquals('Test Document', $document->title);
        $this->assertTrue($document->isDraft());
        $this->assertFalse($document->isGenerated());
        $this->assertTrue($document->isPdf());
    }

    public function test_document_relationships_work(): void
    {
        $user = User::factory()->create();
        $template = DocumentTemplate::factory()->create();

        $document = Document::create([
            'title' => 'Test Document',
            'content' => 'Test content',
            'status' => 'draft',
            'format' => 'pdf',
            'document_template_id' => $template->id,
            'documentable_type' => 'App\Models\Order',
            'documentable_id' => 1,
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(DocumentTemplate::class, $document->template);
        $this->assertInstanceOf(User::class, $document->creator);
        $this->assertEquals($template->id, $document->template->id);
        $this->assertEquals($user->id, $document->creator->id);
    }

    public function test_document_model_methods(): void
    {
        $user = User::factory()->create();
        $template = DocumentTemplate::factory()->create();

        $document = Document::create([
            'title' => 'Test Document',
            'content' => 'Test content',
            'status' => 'draft',
            'format' => 'pdf',
            'document_template_id' => $template->id,
            'documentable_type' => 'App\Models\Order',
            'documentable_id' => 1,
            'created_by' => $user->id,
        ]);

        $this->assertTrue($document->isDraft());
        $this->assertFalse($document->isGenerated());
        $this->assertFalse($document->isPublished());
        $this->assertTrue($document->isPdf());
        $this->assertNull($document->getFileUrl());

        // Test status changes
        $document->update(['status' => 'generated', 'generated_at' => now()]);
        $this->assertTrue($document->isGenerated());
        $this->assertFalse($document->isDraft());

        $document->update(['status' => 'published']);
        $this->assertTrue($document->isPublished());
        $this->assertTrue($document->isGenerated());  // isGenerated returns true for both 'generated' and 'published'
    }
}
