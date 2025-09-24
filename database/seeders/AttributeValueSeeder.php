<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Database\Seeder;

final class AttributeValueSeeder extends Seeder
{
    public function run(): void
    {
        $this->createColorValues();
        $this->createSizeValues();
        $this->createMaterialValues();
        $this->createBrandValues();

        $this->command->info('Attribute values seeded successfully!');
    }

    private function createColorValues(): void
    {
        $colorAttribute = Attribute::where('slug', 'color')->first();
        $product = Product::first();

        if (! $colorAttribute || ! $product) {
            return;
        }

        $colors = [
            ['value' => 'Red', 'display_value' => 'R', 'color_code' => '#FF0000'],
            ['value' => 'Blue', 'display_value' => 'B', 'color_code' => '#0000FF'],
            ['value' => 'Green', 'display_value' => 'G', 'color_code' => '#00FF00'],
            ['value' => 'Black', 'display_value' => 'Bl', 'color_code' => '#000000'],
            ['value' => 'White', 'display_value' => 'W', 'color_code' => '#FFFFFF'],
        ];

        foreach ($colors as $index => $color) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $colorAttribute->id,
                'value' => $color['value'],
            ], [
                'slug' => \Illuminate\Support\Str::slug($color['value']),
                'display_value' => $color['display_value'],
                'color_code' => $color['color_code'],
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function createSizeValues(): void
    {
        $sizeAttribute = Attribute::where('slug', 'size')->first();
        $product = Product::first();

        if (! $sizeAttribute || ! $product) {
            return;
        }

        $sizes = [
            ['value' => 'XS', 'display_value' => 'Extra Small'],
            ['value' => 'S', 'display_value' => 'Small'],
            ['value' => 'M', 'display_value' => 'Medium'],
            ['value' => 'L', 'display_value' => 'Large'],
            ['value' => 'XL', 'display_value' => 'Extra Large'],
        ];

        foreach ($sizes as $index => $size) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $sizeAttribute->id,
                'value' => $size['value'],
            ], [
                'slug' => \Illuminate\Support\Str::slug($size['value']),
                'display_value' => $size['display_value'],
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function createMaterialValues(): void
    {
        $materialAttribute = Attribute::where('slug', 'material')->first();
        $product = Product::first();

        if (! $materialAttribute || ! $product) {
            return;
        }

        $materials = [
            ['value' => 'Cotton', 'display_value' => 'Cot'],
            ['value' => 'Polyester', 'display_value' => 'Poly'],
            ['value' => 'Wool', 'display_value' => 'Wool'],
            ['value' => 'Silk', 'display_value' => 'Silk'],
        ];

        foreach ($materials as $index => $material) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $materialAttribute->id,
                'value' => $material['value'],
            ], [
                'slug' => \Illuminate\Support\Str::slug($material['value']),
                'display_value' => $material['display_value'],
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function createBrandValues(): void
    {
        $brandAttribute = Attribute::where('slug', 'brand')->first();
        $product = Product::first();

        if (! $brandAttribute || ! $product) {
            return;
        }

        $brands = [
            ['value' => 'Nike', 'display_value' => 'Nike'],
            ['value' => 'Adidas', 'display_value' => 'Adidas'],
            ['value' => 'Puma', 'display_value' => 'Puma'],
            ['value' => 'Reebok', 'display_value' => 'Reebok'],
        ];

        foreach ($brands as $index => $brand) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $brandAttribute->id,
                'value' => $brand['value'],
            ], [
                'slug' => \Illuminate\Support\Str::slug($brand['value']),
                'display_value' => $brand['display_value'],
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
