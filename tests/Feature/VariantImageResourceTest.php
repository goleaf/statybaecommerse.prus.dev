<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\VariantImageResource;
use App\Filament\Resources\VariantImageResource\Pages\CreateVariantImage;
use App\Filament\Resources\VariantImageResource\Pages\EditVariantImage;
use App\Filament\Resources\VariantImageResource\Pages\ListVariantImages;
use App\Filament\Resources\VariantImageResource\Pages\ViewVariantImage;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantImage;
use App\Support\Storage\SecureStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class VariantImageResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authenticated administrator account for driving Filament interactions.
     */
    private User $adminUser;

    /**
     * Canonical product variant reference reused across individual scenarios.
     */
    private ProductVariant $productVariant;

    /**
     * Seeded variant image baseline that actions and filters operate against.
     */
    private VariantImage $variantImage;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament resolves the admin panel configuration required for resource pages.
        $this->resolveAdminPanel();


        // Provision an administrator so every table/form assertion runs with the correct guard.
        $this->adminUser = User::factory()->create([
            'email'    => 'admin@example.com',
            'name'     => 'Admin User',
            'is_admin' => true,
        ]);

        // Switch the test guard to the privileged user before interacting with Livewire components.
        $this->actingAs($this->adminUser);

        // Create test product variant
        $this->productVariant = ProductVariant::factory()->create([
            'name' => 'Test Variant',
            'sku'  => 'TEST-SKU-001',
        ]);

        // Create test variant image
        $this->variantImage = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'image_path' => 'test-image.jpg',
            'alt_text'   => 'Test Image',
            'sort_order' => 1,
            'is_primary' => true,
            'is_active'  => true,
        ]);
    }

    public function test_can_list_variant_images(): void
    {
        Livewire::test(ListVariantImages::class)
            ->assertCanSeeTableRecords([$this->variantImage])
            ->assertCanRenderTableColumn('image_path')
            ->assertCanRenderTableColumn('variant.name')
            ->assertCanRenderTableColumn('alt_text')
            ->assertCanRenderTableColumn('sort_order')
            ->assertCanRenderTableColumn('is_primary')
            ->assertCanRenderTableColumn('is_active');
    }

    public function test_can_create_variant_image(): void
    {
        // Fake the secure storage disk that persists finalized variant images while
        // leaving Livewire's temporary disk untouched to preserve MIME detection.
        Storage::fake(SecureStorage::disk());

        $imageFile = $this->makeUploadedImage('test-image.jpg', 800, 600);

        // Pre-seed the secure disk so the form can reference the asset without invoking Livewire uploads.
        $storedPath = 'variant-images/' . $imageFile->getClientOriginalName();
        Storage::disk(SecureStorage::disk())->put($storedPath, file_get_contents($imageFile->getPathname()));

        $this->assertTrue(Storage::disk(SecureStorage::disk())->exists($storedPath));
        $this->assertNotFalse(getimagesize(Storage::disk(SecureStorage::disk())->path($storedPath)));


        $component = Livewire::test(CreateVariantImage::class);

        $component->fillForm([
            'variant_id'  => $this->productVariant->id,
            'image_path'  => [$storedPath],
            'alt_text'    => 'New Test Image',
            'description' => 'Test description',
            'sort_order'  => 2,
            'is_primary'  => false,
            'is_active'   => true,
        ]);

        $component
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_images', [
            'variant_id' => $this->productVariant->id,
            'alt_text'   => 'New Test Image',
            'sort_order' => 2,
            'is_primary' => false,
            'is_active'  => true,
        ]);
    }

    public function test_metadata_is_populated_for_uploaded_images(): void
    {
        // Fake the secure storage disk that persists finalized variant images while
        // leaving Livewire's temporary disk untouched to preserve MIME detection.
        Storage::fake(SecureStorage::disk());

        $imageFile = $this->makeUploadedImage('meta-image.jpg', 320, 240);

        // Pre-seed the secure disk so the form can reference the asset without invoking Livewire uploads.
        $storedPath = 'variant-images/' . $imageFile->getClientOriginalName();
        Storage::disk(SecureStorage::disk())->put($storedPath, file_get_contents($imageFile->getPathname()));

        $component = Livewire::test(CreateVariantImage::class);

        $component->fillForm([
            'variant_id'  => $this->productVariant->id,
            'image_path'  => [$storedPath],
            'alt_text'    => 'Metadata Image',
            'description' => 'Image with metadata',
            'sort_order'  => 3,
            'is_primary'  => false,
            'is_active'   => true,
        ]);

        $component
            ->call('create')
            ->assertHasNoFormErrors();

        $metadata = VariantImageResource::populateFileMetadata(['image_path' => [$storedPath]]);
        $this->assertSame('320×240', $metadata['dimensions'] ?? null);

        $createdImage = VariantImage::query()
            ->where('variant_id', $this->productVariant->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($createdImage);
        $this->assertNotNull($createdImage->file_size);
        $this->assertSame('320×240', $createdImage->dimensions);
    }

    public function test_can_edit_variant_image(): void
    {
        Livewire::test(EditVariantImage::class, ['record' => $this->variantImage->id])
            ->fillForm([
                'image_path'  => [$this->variantImage->image_path],
                'alt_text'    => 'Updated Alt Text',
                'description' => 'Updated description',
                'sort_order'  => 5,
                'is_primary'  => false,
                'is_active'   => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_images', [
            'id'         => $this->variantImage->id,
            'alt_text'   => 'Updated Alt Text',
            'sort_order' => 5,
            'is_primary' => false,
            'is_active'  => false,
        ]);
    }

    public function test_can_view_variant_image(): void
    {
        Livewire::test(ViewVariantImage::class, ['record' => $this->variantImage->id])
            // Confirm the Livewire view page hydrates the expected record instance.
            ->assertSet('record.id', $this->variantImage->id);
    }

    public function test_can_delete_variant_image(): void
    {
        Livewire::test(ListVariantImages::class)
            ->callTableAction('delete', $this->variantImage)
            ->assertHasNoTableActionErrors();

        // Soft deletes keep the row for auditing, so assert the tombstone column is populated.
        $this->assertSoftDeleted('variant_images', [
            'id' => $this->variantImage->id,
        ]);
    }

    public function test_can_set_as_primary_action(): void
    {
        // Create another image for the same variant
        $anotherImage = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_primary' => false,
        ]);

        Livewire::test(ListVariantImages::class)
            ->callTableAction('set_as_primary', $anotherImage);

        // Check that the new image is now primary
        $this->assertDatabaseHas('variant_images', [
            'id'         => $anotherImage->id,
            'is_primary' => true,
        ]);

        // Check that the old primary image is no longer primary
        $this->assertDatabaseHas('variant_images', [
            'id'         => $this->variantImage->id,
            'is_primary' => false,
        ]);
    }

    public function test_can_toggle_active_action(): void
    {
        Livewire::test(ListVariantImages::class)
            ->callTableAction('toggle_active', $this->variantImage);

        $this->assertDatabaseHas('variant_images', [
            'id'        => $this->variantImage->id,
            'is_active' => false,
        ]);
    }

    public function test_can_duplicate_variant_image(): void
    {
        Livewire::test(ListVariantImages::class)
            ->callTableAction('duplicate', $this->variantImage);

        $this->assertDatabaseHas('variant_images', [
            'variant_id' => $this->variantImage->variant_id,
            'alt_text'   => $this->variantImage->alt_text,
            'is_primary' => false, // Duplicated image should not be primary
        ]);
    }

    public function test_can_filter_by_variant(): void
    {
        // Create another variant and image
        $anotherVariant = ProductVariant::factory()->create();
        $anotherImage = VariantImage::factory()->create([
            'variant_id' => $anotherVariant->id,
        ]);

        Livewire::test(ListVariantImages::class)
            ->filterTable('variant_id', $this->productVariant->id)
            ->assertCanSeeTableRecords([$this->variantImage])
            ->assertCanNotSeeTableRecords([$anotherImage]);
    }

    public function test_can_filter_by_primary_status(): void
    {
        // Create a non-primary image
        $nonPrimaryImage = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_primary' => false,
        ]);

        Livewire::test(ListVariantImages::class)
            ->filterTable('is_primary', true)
            ->assertCanSeeTableRecords([$this->variantImage])
            ->assertCanNotSeeTableRecords([$nonPrimaryImage]);
    }

    public function test_can_filter_by_active_status(): void
    {
        // Create an inactive image
        $inactiveImage = VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'is_active'  => false,
        ]);

        Livewire::test(ListVariantImages::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$this->variantImage])
            ->assertCanNotSeeTableRecords([$inactiveImage]);
    }

    public function test_can_bulk_activate_images(): void
    {
        // Create inactive images
        $inactiveImages = VariantImage::factory()->count(3)->create([
            'variant_id' => $this->productVariant->id,
            'is_active'  => false,
        ]);

        Livewire::test(ListVariantImages::class)
            ->callTableBulkAction('activate_selected', $inactiveImages);

        foreach ($inactiveImages as $image) {
            $this->assertDatabaseHas('variant_images', [
                'id'        => $image->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_images(): void
    {
        // Create active images
        $activeImages = VariantImage::factory()->count(3)->create([
            'variant_id' => $this->productVariant->id,
            'is_active'  => true,
        ]);

        Livewire::test(ListVariantImages::class)
            ->callTableBulkAction('deactivate_selected', $activeImages);

        foreach ($activeImages as $image) {
            $this->assertDatabaseHas('variant_images', [
                'id'        => $image->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_can_bulk_reorder_images(): void
    {
        // Create images with different sort orders
        $images = VariantImage::factory()->count(3)->create([
            'variant_id' => $this->productVariant->id,
            'sort_order' => 0, // All with same sort order
        ]);

        Livewire::test(ListVariantImages::class)
            ->callTableBulkAction('reorder_images', $images);

        // Check that images are reordered
        $reorderedImages = VariantImage::whereIn('id', $images->pluck('id'))->orderBy('sort_order')->get();
        foreach ($reorderedImages as $index => $image) {
            $this->assertEquals($index + 1, $image->sort_order);
        }
    }

    public function test_can_bulk_set_primary_images(): void
    {
        // Create images from different variants
        $variant1 = ProductVariant::factory()->create();
        $variant2 = ProductVariant::factory()->create();

        $images = collect([
            VariantImage::factory()->create(['variant_id' => $variant1->id, 'is_primary' => false]),
            VariantImage::factory()->create(['variant_id' => $variant2->id, 'is_primary' => false]),
        ]);

        Livewire::test(ListVariantImages::class)
            ->callTableBulkAction('set_primary', $images);

        // Check that first image of each variant is now primary
        $this->assertDatabaseHas('variant_images', [
            'id'         => $images->first()->id,
            'is_primary' => true,
        ]);
    }

    public function test_can_bulk_delete_images(): void
    {
        // Create images to delete
        $imagesToDelete = VariantImage::factory()->count(3)->create([
            'variant_id' => $this->productVariant->id,
        ]);

        Livewire::test(ListVariantImages::class)
            ->callTableBulkAction('delete', $imagesToDelete)
            ->assertHasNoTableBulkActionErrors();

        foreach ($imagesToDelete as $image) {
            // Each bulk-deleted image should carry a deleted_at timestamp thanks to soft deletes.
            $this->assertSoftDeleted('variant_images', [
                'id' => $image->id,
            ]);
        }
    }

    public function test_form_validation_works(): void
    {
        Livewire::test(CreateVariantImage::class)
            ->fillForm([
                'variant_id' => null, // Required field
                'image_path' => null, // Required field
            ])
            ->call('create')
            ->assertHasFormErrors(['variant_id', 'image_path']);
    }

    public function test_auto_sort_order_generation(): void
    {
        // Create images with specific sort orders
        VariantImage::factory()->create([
            'variant_id' => $this->productVariant->id,
            'sort_order' => 5,
        ]);

        Livewire::test(CreateVariantImage::class)
            ->fillForm([
                'variant_id' => $this->productVariant->id,
            ])
            ->assertFormSet('sort_order', 6); // Should auto-generate next sort order
    }

    public function test_navigation_label_is_correct(): void
    {
        $this->assertEquals(
            __('admin.variant_images.navigation_label'),
            VariantImageResource::getNavigationLabel()
        );
    }

    public function test_model_label_is_correct(): void
    {
        $this->assertEquals(
            __('admin.variant_images.model_label'),
            VariantImageResource::getModelLabel()
        );
    }

    public function test_plural_model_label_is_correct(): void
    {
        $this->assertEquals(
            __('admin.variant_images.plural_model_label'),
            VariantImageResource::getPluralModelLabel()
        );
    }

    /**
     * Construct a real JPEG upload instance so validation can inspect genuine image metadata.
     */
    private function makeUploadedImage(string $name, int $width, int $height): UploadedFile
    {
        // Populate a simple solid-colour canvas to produce deterministic dimensions for assertions.
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 240, 240, 240));

        ob_start();
        imagejpeg($image);
        $imageContents = (string) ob_get_clean();
        imagedestroy($image);

        return UploadedFile::fake()->createWithContent($name, $imageContents);
    }
}
