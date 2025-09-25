<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\BrandTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        $locales = ['lt', 'en'];

        $suffix = fn(string $value, string $locale): string => $value . ' [' . $locale . ']';

        Brand::query()->with('translations')->each(function (Brand $brand) use ($locales, $suffix): void {
            foreach ($locales as $locale) {
                if ($brand->hasTranslationFor($locale)) {
                    continue;
                }

                // Use factory to create translation with relationship
                $brand->translations()->firstOrCreate(
                    ['locale' => $locale],
                    BrandTranslation::factory()
                        ->for($brand, 'brand')
                        ->make([
                            'locale' => $locale,
                            'name' => $suffix($brand->name, $locale),
                            'slug' => $suffix($brand->slug, $locale),
                            'description' => $locale === 'lt'
                                ? 'Aprašymas apie ' . $brand->name . ' prekės ženklą lietuvių kalba.'
                                : 'Description about ' . $brand->name . ' brand in English.',
                            'seo_title' => $locale === 'lt'
                                ? $brand->name . ' - Aukštos kokybės statybos įrankiai'
                                : $brand->name . ' - High Quality Construction Tools',
                            'seo_description' => $locale === 'lt'
                                ? 'Pirkite ' . $brand->name . ' produktus mūsų internetinėje parduotuvėje. Aukštos kokybės statybos įrankiai ir medžiagos.'
                                : 'Buy ' . $brand->name . ' products in our online store. High quality construction tools and materials.',
                        ])
                        ->toArray()
                );
            }
        });

        Category::query()->with('translations')->each(function (Category $category) use ($locales, $suffix): void {
            foreach ($locales as $locale) {
                if ($category->hasTranslationFor($locale)) {
                    continue;
                }

                // Use factory to create translation with relationship
                $category->translations()->firstOrCreate(
                    ['locale' => $locale],
                    CategoryTranslation::factory()
                        ->for($category, 'category')
                        ->make([
                            'locale' => $locale,
                            'name' => $suffix($category->name, $locale),
                            'slug' => $suffix($category->slug, $locale),
                            'description' => $locale === 'lt'
                                ? 'Aprašymas lietuvių kalba. Čia rasite visus reikalingus produktus.'
                                : 'Category ' . $category->name . ' description in English. Here you will find all necessary products.',
                            'seo_title' => $locale === 'lt'
                                ? $category->name . ' - Statybos įrankiai ir medžiagos'
                                : $category->name . ' - Construction Tools and Materials',
                            'seo_description' => $locale === 'lt'
                                ? 'Platus ' . $category->name . ' asortimentas. Aukštos kokybės produktai geriausiomis kainomis.'
                                : 'Wide ' . $category->name . ' assortment. High quality products at the best prices.',
                        ])
                        ->toArray()
                );
            }
        });

        Collection::query()->with('translations')->each(function (Collection $collection) use ($locales, $suffix): void {
            foreach ($locales as $locale) {
                if ($collection->hasTranslationFor($locale)) {
                    continue;
                }

                // Use factory to create translation with relationship
                $collection->translations()->firstOrCreate(
                    ['locale' => $locale],
                    CollectionTranslation::factory()
                        ->for($collection, 'collection')
                        ->make([
                            'locale' => $locale,
                            'name' => $suffix($collection->name, $locale),
                            'slug' => $suffix($collection->slug, $locale),
                            'description' => $locale === 'lt'
                                ? 'Aprašymas lietuvių kalba. Specialiai atrinkti produktai.'
                                : 'Collection ' . $collection->name . ' description in English. Specially selected products.',
                        ])
                        ->toArray()
                );
            }
        });

        Product::query()->with('translations')->each(function (Product $product) use ($locales, $suffix): void {
            foreach ($locales as $locale) {
                if ($product->hasTranslationFor($locale)) {
                    continue;
                }

                // Use factory to create translation with relationship
                $product->translations()->firstOrCreate(
                    ['locale' => $locale],
                    ProductTranslation::factory()
                        ->for($product, 'product')
                        ->make([
                            'locale' => $locale,
                            'name' => $suffix($product->name, $locale),
                            'slug' => $suffix($product->slug, $locale),
                            'summary' => $product->short_description
                                ? $suffix($product->short_description, $locale)
                                : ($locale === 'lt'
                                    ? 'Trumpas produkto aprašymas lietuvių kalba.'
                                    : 'Short product description in English.'),
                            'description' => $locale === 'lt'
                                ? '<p>Detalus produkto <strong>' . $product->name . '</strong> aprašymas lietuvių kalba.</p><p>Šis produktas pasižymi aukšta kokybe ir patikimumu. Idealiai tinka profesionaliam ir buitiniam naudojimui.</p><ul><li>Aukšta kokybė</li><li>Patikimumas</li><li>Lengvas naudojimas</li><li>Ilgas tarnavimo laikas</li></ul>'
                                : '<p>Detailed product <strong>' . $product->name . '</strong> description in English.</p><p>This product is characterized by high quality and reliability. Ideally suited for professional and domestic use.</p><ul><li>High quality</li><li>Reliability</li><li>Easy to use</li><li>Long service life</li></ul>',
                            'seo_title' => $locale === 'lt'
                                ? $product->name . ' - Aukštos kokybės statybos įrankis'
                                : $product->name . ' - High Quality Construction Tool',
                            'seo_description' => $locale === 'lt'
                                ? 'Pirkite ' . $product->name . ' mūsų internetinėje parduotuvėje. Greitas pristatymas, geriausia kaina.'
                                : 'Buy ' . $product->name . ' in our online store. Fast delivery, best price.',
                        ])
                        ->toArray()
                );
            }
        });

        if (class_exists(Legal::class)) {
            Legal::query()->with('translations')->each(function ($legal) use ($locales, $suffix): void {
                foreach ($locales as $locale) {
                    if ($legal->hasTranslationFor($locale)) {
                        continue;
                    }

                    $legal->updateTranslation($locale, [
                        'title' => $suffix($legal->title, $locale),
                        'slug' => $suffix($legal->slug, $locale),
                        'content' => '<p>' . e($legal->title) . ' [' . $locale . ']</p>',
                    ]);
                }
            });
        }
    }
}
