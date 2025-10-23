<?php

declare(strict_types=1);

namespace Tests\Unit\PHP;

use App\Domain\Product\Collections\ProductImageCollection;
use App\Domain\Product\Collections\ProductVariantCollection;
use App\Domain\Product\Entities\Product as DomainProduct;
use App\Models\CartItem;
use Illuminate\Support\Arr;
use Tests\Unit\UnitTestCase;

final class ArrayOffsetsAndCollectionsTest extends UnitTestCase
{
    public function test_arr_get_on_nullable_array_returns_null_without_error(): void
    {
        $x = null;

        $this->assertNull(Arr::get($x, 'key'));
    }

    public function test_domain_product_accessors_handle_nullable_arrays(): void
    {
        $product = new DomainProduct(
            id: 1,
            name: 'Test',
            slug: 'test',
            sku: 'SKU-1',
            price: 10.0,
            salePrice: null,
            brand: null,
            category: null,
            isVisible: true,
            isFeatured: false,
            manageStock: false,
            isInStock: true,
            stockQuantity: 0,
            images: new ProductImageCollection(),
            variants: new ProductVariantCollection(),
        );

        $this->assertNull($product->getBrandName());
        $this->assertNull($product->getCategoryName());
    }

    public function test_cart_item_accessors_handle_null_snapshot(): void
    {
        $item = new CartItem(['product_snapshot' => null]);

        $this->assertNull($item->product_name);
        $this->assertNull($item->product_sku);
    }
}

