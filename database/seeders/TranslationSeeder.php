<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        $locales = ['lt'];

        // Helper to upsert translations with a suffix per locale
        $suffix = function (string $value, string $locale): string {
            return $value . ' [' . $locale . ']';
        };

        // Brands
        $brands = DB::table('sh_brands')->get(['id', 'name', 'slug']);
        foreach ($brands as $brand) {
            foreach ($locales as $loc) {
                $exists = DB::table('sh_brand_translations')->where('brand_id', $brand->id)->where('locale', $loc)->exists();
                if (!$exists) {
                    DB::table('sh_brand_translations')->insert([
                        'brand_id' => $brand->id,
                        'locale' => $loc,
                        'name' => $suffix($brand->name, $loc),
                        'slug' => $suffix($brand->slug, $loc),
                        'description' => null,
                        'seo_title' => null,
                        'seo_description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Categories
        $categories = DB::table('sh_categories')->get(['id', 'name', 'slug']);
        foreach ($categories as $cat) {
            foreach ($locales as $loc) {
                $exists = DB::table('sh_category_translations')->where('category_id', $cat->id)->where('locale', $loc)->exists();
                if (!$exists) {
                    DB::table('sh_category_translations')->insert([
                        'category_id' => $cat->id,
                        'locale' => $loc,
                        'name' => $suffix($cat->name, $loc),
                        'slug' => $suffix($cat->slug, $loc),
                        'description' => null,
                        'seo_title' => null,
                        'seo_description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Collections
        $collections = DB::table('sh_collections')->get(['id', 'name', 'slug']);
        foreach ($collections as $col) {
            foreach ($locales as $loc) {
                $exists = DB::table('sh_collection_translations')->where('collection_id', $col->id)->where('locale', $loc)->exists();
                if (!$exists) {
                    DB::table('sh_collection_translations')->insert([
                        'collection_id' => $col->id,
                        'locale' => $loc,
                        'name' => $suffix($col->name, $loc),
                        'slug' => $suffix($col->slug, $loc),
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Products
        $products = DB::table('sh_products')->get(['id', 'name', 'slug', 'summary']);
        foreach ($products as $p) {
            foreach ($locales as $loc) {
                $exists = DB::table('sh_product_translations')->where('product_id', $p->id)->where('locale', $loc)->exists();
                if (!$exists) {
                    DB::table('sh_product_translations')->insert([
                        'product_id' => $p->id,
                        'locale' => $loc,
                        'name' => $suffix($p->name, $loc),
                        'slug' => $suffix($p->slug, $loc),
                        'summary' => $p->summary ? $suffix($p->summary, $loc) : null,
                        'description' => null,
                        'seo_title' => null,
                        'seo_description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Legal
        $legals = DB::table('sh_legals')->get(['id', 'title', 'slug']);
        foreach ($legals as $l) {
            foreach ($locales as $loc) {
                $exists = DB::table('sh_legal_translations')->where('legal_id', $l->id)->where('locale', $loc)->exists();
                if (!$exists) {
                    DB::table('sh_legal_translations')->insert([
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
