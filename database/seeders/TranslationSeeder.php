<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        $locales = ['lt', 'en'];

        // Helper to upsert translations with a suffix per locale
        $suffix = function (string $value, string $locale): string {
            return $value . ' [' . $locale . ']';
        };

        // Brands
        $brands = DB::table('brands')->get(['id', 'name', 'slug']);
        foreach ($brands as $brand) {
            foreach ($locales as $loc) {
                $exists = DB::table('brand_translations')->where('brand_id', $brand->id)->where('locale', $loc)->exists();
                if (!$exists) {
                    DB::table('brand_translations')->insert([
                        'brand_id' => $brand->id,
                        'locale' => $loc,
                        'name' => $suffix($brand->name, $loc),
                        'slug' => $suffix($brand->slug, $loc),
                        'description' => $loc === 'lt'
                            ? 'Aprašymas apie ' . $brand->name . ' prekės ženklą lietuvių kalba.'
                            : 'Description about ' . $brand->name . ' brand in English.',
                        'seo_title' => $loc === 'lt'
                            ? $brand->name . ' - Aukštos kokybės statybos įrankiai'
                            : $brand->name . ' - High Quality Construction Tools',
                        'seo_description' => $loc === 'lt'
                            ? 'Pirkite ' . $brand->name . ' produktus mūsų internetinėje parduotuvėje. Aukštos kokybės statybos įrankiai ir medžiagos.'
                            : 'Buy ' . $brand->name . ' products in our online store. High quality construction tools and materials.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Categories
        $categories = DB::table('categories')->get(['id', 'name', 'slug']);
        foreach ($categories as $cat) {
            foreach ($locales as $loc) {
                $exists = DB::table('category_translations')->where('category_id', $cat->id)->where('locale', $loc)->exists();
                if (!$exists) {
                    DB::table('category_translations')->insert([
                        'category_id' => $cat->id,
                        'locale' => $loc,
                        'name' => $suffix($cat->name, $loc),
                        'slug' => $suffix($cat->slug, $loc),
                        'description' => $loc === 'lt'
                            ? 'Kategorijos ' . $cat->name . ' aprašymas lietuvių kalba. Čia rasite visus reikalingus produktus.'
                            : 'Category ' . $cat->name . ' description in English. Here you will find all necessary products.',
                        'seo_title' => $loc === 'lt'
                            ? $cat->name . ' - Statybos įrankiai ir medžiagos'
                            : $cat->name . ' - Construction Tools and Materials',
                        'seo_description' => $loc === 'lt'
                            ? 'Platus ' . $cat->name . ' asortimentas. Aukštos kokybės produktai geriausiomis kainomis.'
                            : 'Wide ' . $cat->name . ' assortment. High quality products at the best prices.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Collections
        $collections = DB::table('collections')->get(['id', 'name', 'slug']);
        foreach ($collections as $col) {
            foreach ($locales as $loc) {
                $exists = DB::table('collection_translations')->where('collection_id', $col->id)->where('locale', $loc)->exists();
                if (!$exists) {
                    DB::table('collection_translations')->insert([
                        'collection_id' => $col->id,
                        'locale' => $loc,
                        'name' => $suffix($col->name, $loc),
                        'slug' => $suffix($col->slug, $loc),
                        'description' => $loc === 'lt'
                            ? 'Kolekcijos ' . $col->name . ' aprašymas lietuvių kalba. Specialiai atrinkti produktai.'
                            : 'Collection ' . $col->name . ' description in English. Specially selected products.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Products
        $products = DB::table('products')->get(['id', 'name', 'slug', 'short_description']);
        foreach ($products as $p) {
            foreach ($locales as $loc) {
                $exists = DB::table('product_translations')->where('product_id', $p->id)->where('locale', $loc)->exists();
                if (!$exists) {
                    DB::table('product_translations')->insert([
                        'product_id' => $p->id,
                        'locale' => $loc,
                        'name' => $suffix($p->name, $loc),
                        'slug' => $suffix($p->slug, $loc),
                        'summary' => $p->short_description ? $suffix($p->short_description, $loc) : ($loc === 'lt' ? 'Trumpas produkto aprašymas lietuvių kalba.' : 'Short product description in English.'),
                        'description' => $loc === 'lt'
                            ? '<p>Detalus produkto <strong>' . $p->name . '</strong> aprašymas lietuvių kalba.</p><p>Šis produktas pasižymi aukšta kokybe ir patikimumu. Idealiai tinka profesionaliam ir buitiniam naudojimui.</p><ul><li>Aukšta kokybė</li><li>Patikimumas</li><li>Lengvas naudojimas</li><li>Ilgas tarnavimo laikas</li></ul>'
                            : '<p>Detailed product <strong>' . $p->name . '</strong> description in English.</p><p>This product is characterized by high quality and reliability. Ideally suited for professional and domestic use.</p><ul><li>High quality</li><li>Reliability</li><li>Easy to use</li><li>Long service life</li></ul>',
                        'seo_title' => $loc === 'lt'
                            ? $p->name . ' - Aukštos kokybės statybos įrankis'
                            : $p->name . ' - High Quality Construction Tool',
                        'seo_description' => $loc === 'lt'
                            ? 'Pirkite ' . $p->name . ' mūsų internetinėje parduotuvėje. Greitas pristatymas, geriausia kaina.'
                            : 'Buy ' . $p->name . ' in our online store. Fast delivery, best price.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Legal (if exists)
        if (DB::getSchemaBuilder()->hasTable('legals')) {
            $legals = DB::table('legals')->get(['id', 'title', 'slug']);
            foreach ($legals as $l) {
                foreach ($locales as $loc) {
                    $exists = DB::table('legal_translations')->where('legal_id', $l->id)->where('locale', $loc)->exists();
                    if (!$exists) {
                        DB::table('legal_translations')->insert([
                            'legal_id' => $l->id,
                            'locale' => $loc,
                            'title' => $suffix($l->title, $loc),
                            'slug' => $suffix($l->slug, $loc),
                            'content' => '<p>' . e($l->title) . ' [' . $loc . ']</p>',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
