<?php

declare(strict_types=1);

namespace Tests\Feature\Views;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductImageComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_displays_provided_image(): void
    {
        $imagePath = 'product-images/example.jpg';

        $view = $this->blade('<x-product-image :image="$path" alt="Preview" />', [
            'path' => $imagePath,
        ]);

        $view->assertSee('product-images/example.jpg', false);
        $view->assertSee('alt="Preview"', false);
    }

    public function test_component_displays_fallback_when_image_missing(): void
    {
        $view = $this->blade('<x-product-image :image="null" />');

        $view->assertSeeText(__('admin.no_image'));
    }
}
