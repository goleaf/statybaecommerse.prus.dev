<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\RelationManagers\OrderDocumentsRelationManager;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * OrderDocumentsRelationManagerTest
 *
 * Comprehensive test suite for OrderDocumentsRelationManager with Filament v4 compatibility
 */
final class OrderDocumentsRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->order = Order::factory()->create(['user_id' => $this->user->id]);
    }

    /**
     * @test
     */
    public function it_can_render_order_documents_relation_manager(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(OrderDocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass' => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component->assertSuccessful();
    }

    /**
     * @test
     */
    public function it_can_create_order_document(): void
    {
        $this->actingAs($this->user);

        Storage::fake();
        $template = DocumentTemplate::factory()->create();

        $component = Livewire::test(OrderDocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass' => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->mountTableAction('create')
            ->assertFormExists()
            ->fillForm([
                'document_template_id' => $template->id,
                'name' => 'Test Document',
                'type' => 'invoice',
                'version' => '1.0',
                'status' => 'draft',
                'is_public' => false,
                'is_downloadable' => true,
                'description' => 'Test document description',
            ])
            ->callMountedTableAction()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('documents', [
            'documentable_type' => \App\Models\Order::class,
            'documentable_id' => $this->order->id,
            'name' => 'Test Document',
            'type' => 'invoice',
            'status' => 'draft',
        ]);
    }

    /**
     * @test
     */
    public function it_can_approve_document(): void
    {
        $this->actingAs($this->user);

        $document = Document::factory()->create([
            'documentable_type' => \App\Models\Order::class,
            'documentable_id' => $this->order->id,
            'status' => 'pending',
        ]);

        $component = Livewire::test(OrderDocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass' => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableAction('approve', $document)
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'approved',
        ]);
    }

    /**
     * @test
     */
    public function it_can_reject_document(): void
    {
        $this->actingAs($this->user);

        $document = Document::factory()->create([
            'documentable_type' => \App\Models\Order::class,
            'documentable_id' => $this->order->id,
            'status' => 'pending',
        ]);

        $component = Livewire::test(OrderDocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass' => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableAction('reject', $document)
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'rejected',
        ]);
    }

    /**
     * @test
     */
    public function it_can_filter_by_document_type(): void
    {
        $this->actingAs($this->user);

        Document::factory()->create([
            'documentable_type' => \App\Models\Order::class,
            'documentable_id' => $this->order->id,
            'type' => 'invoice',
        ]);

        Document::factory()->create([
            'documentable_type' => \App\Models\Order::class,
            'documentable_id' => $this->order->id,
            'type' => 'receipt',
        ]);

        $component = Livewire::test(OrderDocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass' => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->filterTable('type', 'invoice')
            ->assertCanSeeTableRecords(
                Document::where('type', 'invoice')->get()
            );
    }

    /**
     * @test
     */
    public function it_can_perform_bulk_approve(): void
    {
        $this->actingAs($this->user);

        $documents = Document::factory()->count(2)->create([
            'documentable_type' => \App\Models\Order::class,
            'documentable_id' => $this->order->id,
            'status' => 'pending',
        ]);

        $component = Livewire::test(OrderDocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass' => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableBulkAction('approve_documents', $documents)
            ->assertHasNoFormErrors();

        foreach ($documents as $document) {
            $this->assertDatabaseHas('documents', [
                'id' => $document->id,
                'status' => 'approved',
            ]);
        }
    }

    /**
     * @test
     */
    public function it_can_perform_bulk_make_public(): void
    {
        $this->actingAs($this->user);

        $documents = Document::factory()->count(2)->create([
            'documentable_type' => \App\Models\Order::class,
            'documentable_id' => $this->order->id,
            'is_public' => false,
        ]);

        $component = Livewire::test(OrderDocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass' => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableBulkAction('make_public', $documents)
            ->assertHasNoFormErrors();

        foreach ($documents as $document) {
            $this->assertDatabaseHas('documents', [
                'id' => $document->id,
                'is_public' => true,
            ]);
        }
    }
}
