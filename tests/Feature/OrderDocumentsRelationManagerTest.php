<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\RelationManagers\DocumentsRelationManager;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OrderDocumentsRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->order = Order::factory()->create(['user_id' => $this->user->id]);
    }

    #[Test]
    public function it_can_render_order_documents_relation_manager(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ])
        ->assertSuccessful();
    }

    #[Test]
    public function it_can_list_documents(): void
    {
        $this->actingAs($this->user);

        $document = Document::factory()->create([
            'documentable_type' => Order::class,
            'documentable_id'   => $this->order->id,
            'title'             => 'Test Invoice',
        ]);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ])
        ->assertCanSeeTableRecords([$document])
        ->assertSee('Test Invoice');
    }

    #[Test]
    public function it_can_view_document(): void
    {
        $this->actingAs($this->user);

        $document = Document::factory()->create([
            'documentable_type' => Order::class,
            'documentable_id'   => $this->order->id,
            'status'            => 'generated',
            'file_path'         => 'documents/test.pdf',
        ]);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ])
        ->assertTableActionExists('view')
        ->assertTableActionVisible('view', $document);
    }

    #[Test]
    public function it_can_download_document(): void
    {
        $this->actingAs($this->user);

        $document = Document::factory()->create([
            'documentable_type' => Order::class,
            'documentable_id'   => $this->order->id,
            'status'            => 'generated',
            'is_downloadable'   => true,
            'file_path'         => 'documents/test.pdf',
        ]);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ])
        ->assertTableActionExists('download')
        ->assertTableActionVisible('download', $document);
    }

    #[Test]
    public function it_hides_download_action_when_not_downloadable(): void
    {
        $this->actingAs($this->user);

        $document = Document::factory()->create([
            'documentable_type' => Order::class,
            'documentable_id'   => $this->order->id,
            'status'            => 'generated',
            'is_downloadable'   => false,
            'file_path'         => 'documents/test.pdf',
        ]);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ])
        ->assertTableActionHidden('download', $document);
    }
}