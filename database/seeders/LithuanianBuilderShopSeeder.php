<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LithuanianBuilderShopSeeder extends Seeder
{
    public function run(): void
    {
        $activityStatus = app(\Spatie\Activitylog\ActivityLogStatus::class);
        $activityStatus->disable();
        try {
            // Create roles and permissions
            $this->createRolesAndPermissions();

            // Create admin users
            $this->createAdminUsers();

            // Create main categories
            $categories = $this->createMainCategories();

            // Create subcategories
            $this->createSubcategories($categories);

            // Create brands
            $brands = $this->createBrands();

            // Create products
            $this->createProducts($brands, $categories);

            // Create sample orders
            $this->createSampleOrders();
        } finally {
            $activityStatus->enable();
        }
    }

    private function createRolesAndPermissions(): void
    {
        // Create permissions
        $permissions = [
            'view_admin_panel',
            'manage_products',
            'manage_categories',
            'manage_brands',
            'manage_orders',
            'manage_users',
            'view_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);

        // Assign permissions to roles
        $adminRole->syncPermissions($permissions);
        $managerRole->syncPermissions(['view_admin_panel', 'manage_products', 'manage_categories', 'manage_orders', 'view_reports']);
    }

    private function createAdminUsers(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@statybaecommerse.lt'],
            [
                'name' => 'Administratorius Sistema',
                'first_name' => 'Administratorius',
                'last_name' => 'Sistema',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'preferred_locale' => 'lt',
            ]
        );
        $admin->assignRole('admin');

        $manager = User::firstOrCreate(
            ['email' => 'manager@statybaecommerse.lt'],
            [
                'name' => 'Vadybininkas Parduotuvės',
                'first_name' => 'Vadybininkas',
                'last_name' => 'Parduotuvės',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'preferred_locale' => 'lt',
            ]
        );
        $manager->assignRole('manager');
    }

    private function createMainCategories(): array
    {
        $mainCategories = [
            [
                'name' => 'Elektriniai įrankiai',
                'slug' => 'elektriniai-irankiai',
                'description' => 'Profesionalūs elektriniai įrankiai statybos ir remonto darbams',
                'sort_order' => 1,
            ],
            [
                'name' => 'Rankiniai įrankiai',
                'slug' => 'rankiniai-irankiai',
                'description' => 'Aukštos kokybės rankiniai įrankiai visoms statybos reikmėms',
                'sort_order' => 2,
            ],
            [
                'name' => 'Statybinės medžiagos',
                'slug' => 'statybines-medziagos',
                'description' => 'Platus statybinių medžiagų asortimentas namų statybai',
                'sort_order' => 3,
            ],
            [
                'name' => 'Saugos priemonės',
                'slug' => 'saugos-priemones',
                'description' => 'Darbuotojų saugos priemonės ir apsauginė įranga',
                'sort_order' => 4,
            ],
            [
                'name' => 'Matavimo įranga',
                'slug' => 'matavimo-iranga',
                'description' => 'Preciziškas matavimo ir žymėjimo įrankiai',
                'sort_order' => 5,
            ],
        ];

        $categories = [];
        foreach ($mainCategories as $categoryData) {
            $existing = Category::where('slug', $categoryData['slug'])->first();
            if ($existing) {
                $categories[] = $existing;
            } else {
                $categories[] = Category::factory()->create($categoryData + [
                    'is_visible' => true,
                    'seo_title' => $categoryData['name'].' - Statybaecommerse.lt',
                    'seo_description' => $categoryData['description'].'. Geriausi sprendimai statybininkams.',
                ]);
            }
        }

        return $categories;
    }

    private function createSubcategories(array $mainCategories): void
    {
        $subcategories = [
            'Elektriniai įrankiai' => [
                'Gręžtuvai ir perforatoriai',
                'Pjūklai ir diskų pjovimo mašinos',
                'Šlifavimo mašinos',
                'Frezavimo įrankiai',
                'Sriegimo įrankiai',
            ],
            'Rankiniai įrankiai' => [
                'Plaktukai ir kalteliai',
                'Raktai ir replės',
                'Atsuktuvai ir kaltai',
                'Matavimo įrankiai',
                'Pjovimo įrankiai',
            ],
            'Statybinės medžiagos' => [
                'Cementas ir betono mišiniai',
                'Gipso plokštės ir profiliai',
                'Izoliacijos medžiagos',
                'Hidroizoliacijos plėvelės',
                'Klijų mišiniai',
            ],
            'Saugos priemonės' => [
                'Apsauginiai šalmai',
                'Darbo pirštinės',
                'Apsauginiai batai',
                'Akių apsaugos',
                'Kvėpavimo apsaugos',
            ],
        ];

        foreach ($subcategories as $parentName => $subs) {
            $parent = collect($mainCategories)->first(fn ($cat) => $cat->name === $parentName);
            if ($parent) {
                foreach ($subs as $index => $subName) {
                    $slug = Str::slug($subName);
                    $existing = Category::where('slug', $slug)->first();
                    
                    if (!$existing) {
                        Category::factory()->create([
                            'name' => $subName,
                            'slug' => $slug,
                            'description' => "Specializuoti {$subName} aukščiausios kokybės",
                            'parent_id' => $parent->id,
                            'sort_order' => $index + 1,
                            'is_visible' => true,
                            'seo_title' => $subName.' - '.$parentName,
                            'seo_description' => "Profesionalūs {$subName} statybos darbams. Platus pasirinkimas ir konkurencingos kainos.",
                        ]);
                    }
                }
            }
        }
    }

    private function createBrands(): array
    {
        $lithuanianBrands = [
            [
                'name' => 'Makita Lietuva',
                'slug' => 'makita-lietuva',
                'description' => 'Japonų kokybės elektriniai įrankiai profesionalams',
                'website' => 'https://makita.lt',
                'is_enabled' => true,
            ],
            [
                'name' => 'Bosch Professional LT',
                'slug' => 'bosch-professional-lt',
                'description' => 'Vokiškas tikslumas ir patikimumas statybos įrankiuose',
                'website' => 'https://bosch.lt',
                'is_enabled' => true,
            ],
            [
                'name' => 'DeWalt Baltics',
                'slug' => 'dewalt-baltics',
                'description' => 'Amerikiečių kokybės įrankiai intensyviam naudojimui',
                'website' => 'https://dewalt.lt',
                'is_enabled' => true,
            ],
            [
                'name' => 'Hilti Lithuania',
                'slug' => 'hilti-lithuania',
                'description' => 'Šveicarų inžinerijos sprendimai statybos pramonei',
                'website' => 'https://hilti.lt',
                'is_enabled' => true,
            ],
            [
                'name' => 'Knauf Lietuva',
                'slug' => 'knauf-lietuva',
                'description' => 'Gipso plokščių ir statybinių mišinių lyderis',
                'website' => 'https://knauf.lt',
                'is_enabled' => true,
            ],
        ];

        $brands = [];
        foreach ($lithuanianBrands as $brandData) {
            $existing = Brand::where('slug', $brandData['slug'])->first();
            if ($existing) {
                $brands[] = $existing;
            } else {
                $brands[] = Brand::factory()->create($brandData + [
                    'seo_title' => $brandData['name'].' - Oficialus atstovas Lietuvoje',
                    'seo_description' => $brandData['description'].'. Originalūs gaminiai su garantija.',
                ]);
            }
        }

        return $brands;
    }

    private function createProducts(array $brands, array $categories): void
    {
        $brandIds = collect($brands)->pluck('id')->toArray();
        $categoryIds = collect($categories)->pluck('id')->toArray();

        // Create 50 products with Lithuanian builder focus using factory relationships
        Product::factory()
            ->count(50)
            ->sequence(fn ($sequence) => [
                'brand_id' => !empty($brandIds) ? fake()->randomElement($brandIds) : null,
            ])
            ->create()
            ->each(function (Product $product) use ($categoryIds) {
                // Assign to 1-2 random categories using factory relationships
                if (!empty($categoryIds)) {
                    $product->categories()->attach(
                        fake()->randomElements($categoryIds, fake()->numberBetween(1, 2))
                    );
                }
            });
    }

    private function createSampleOrders(): void
    {
        // Create some sample customers using factories
        $customers = User::factory()->count(20)->create();

        // Create orders for some customers using factory relationships
        $customers->take(10)->each(function (User $customer) {
            $orderCount = fake()->numberBetween(1, 5);
            
            // Create orders using factory
            Order::factory()
                ->count($orderCount)
                ->for($customer)
                ->create()
                ->each(function (Order $order) {
                    // Add order items using factory
                    $products = Product::inRandomOrder()->take(fake()->numberBetween(1, 5))->get();
                    
                    foreach ($products as $product) {
                        $quantity = fake()->numberBetween(1, 3);
                        $price = $product->sale_price ?? $product->price;

                        OrderItem::factory()->create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'quantity' => $quantity,
                            'unit_price' => $price,
                            'total' => $price * $quantity,
                        ]);
                    }
                });
        });
    }
}
