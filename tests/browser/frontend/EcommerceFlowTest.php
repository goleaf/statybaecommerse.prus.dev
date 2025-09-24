<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class EcommerceFlowTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->category = Category::factory()->create([
            'name' => 'Electronics',
            'is_visible' => true,
        ]);

        $this->brand = Brand::factory()->create([
            'name' => 'Apple',
            'is_enabled' => true,
        ]);

        $this->product = Product::factory()->create([
            'name' => 'iPhone 15 Pro',
            'description' => 'Latest iPhone with advanced features',
            'is_visible' => true,
            'brand_id' => $this->brand->id,
        ]);

        $this->product->categories()->attach($this->category->id);

        $this->variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
            'price' => 99999,  // $999.99
            'stock_quantity' => 10,
        ]);

        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_customer_can_browse_products(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/')
                ->assertSee('Electronics')
                ->assertSee('iPhone 15 Pro')
                ->assertSee('Apple')
                ->assertSee('$999.99');
        });
    }

    public function test_customer_can_view_product_details(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/')
                ->clickLink('iPhone 15 Pro')
                ->waitForLocation('/products/'.$this->product->slug)
                ->assertSee('iPhone 15 Pro')
                ->assertSee('Latest iPhone with advanced features')
                ->assertSee('Apple')
                ->assertSee('$999.99')
                ->assertPresent('[data-add-to-cart-button]');
        });
    }

    public function test_customer_can_search_products(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/')
                ->type('[data-search-input]', 'iPhone')
                ->press('[data-search-button]')
                ->waitFor('[data-search-results]')
                ->assertSee('iPhone 15 Pro')
                ->assertSee('Search results for "iPhone"');
        });
    }

    public function test_customer_can_filter_by_category(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/products')
                ->click('[data-category-filter="'.$this->category->id.'"]')
                ->waitFor('[data-filtered-products]')
                ->assertSee('iPhone 15 Pro')
                ->assertSee('Electronics');
        });
    }

    public function test_customer_can_filter_by_brand(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/products')
                ->click('[data-brand-filter="'.$this->brand->id.'"]')
                ->waitFor('[data-filtered-products]')
                ->assertSee('iPhone 15 Pro')
                ->assertSee('Apple');
        });
    }

    public function test_guest_can_add_product_to_cart(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/products/'.$this->product->slug)
                ->click('[data-add-to-cart-button]')
                ->waitFor('[data-cart-notification]')
                ->assertSee('Added to cart')
                ->assertSee('1', '[data-cart-count]');
        });
    }

    public function test_customer_can_view_cart(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/products/'.$this->product->slug)
                ->click('[data-add-to-cart-button]')
                ->waitFor('[data-cart-notification]')
                ->click('[data-cart-link]')
                ->waitForLocation('/cart')
                ->assertSee('Shopping Cart')
                ->assertSee('iPhone 15 Pro')
                ->assertSee('$999.99');
        });
    }

    public function test_customer_can_update_cart_quantity(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/products/'.$this->product->slug)
                ->click('[data-add-to-cart-button]')
                ->waitFor('[data-cart-notification]')
                ->visit('/cart')
                ->clear('[data-quantity-input]')
                ->type('[data-quantity-input]', '2')
                ->click('[data-update-cart-button]')
                ->waitFor('[data-cart-updated]')
                ->assertSee('2', '[data-quantity-input]')
                ->assertSee('$1,999.98');  // 2 × $999.99
        });
    }

    public function test_customer_can_remove_item_from_cart(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/products/'.$this->product->slug)
                ->click('[data-add-to-cart-button]')
                ->waitFor('[data-cart-notification]')
                ->visit('/cart')
                ->click('[data-remove-item-button]')
                ->waitFor('[data-cart-empty]')
                ->assertSee('Your cart is empty');
        });
    }

    public function test_customer_must_login_to_checkout(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/products/'.$this->product->slug)
                ->click('[data-add-to-cart-button]')
                ->waitFor('[data-cart-notification]')
                ->visit('/cart')
                ->click('[data-checkout-button]')
                ->waitForLocation('/login')
                ->assertSee('Sign in to your account');
        });
    }

    public function test_authenticated_customer_can_proceed_to_checkout(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit('/products/'.$this->product->slug)
                ->click('[data-add-to-cart-button]')
                ->waitFor('[data-cart-notification]')
                ->visit('/cart')
                ->click('[data-checkout-button]')
                ->waitForLocation('/checkout')
                ->assertSee('Checkout')
                ->assertSee('Billing Information');
        });
    }

    public function test_customer_can_complete_checkout_process(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit('/products/'.$this->product->slug)
                ->click('[data-add-to-cart-button]')
                ->waitFor('[data-cart-notification]')
                ->visit('/checkout')
                ->type('[name="billing_address[first_name]"]', 'John')
                ->type('[name="billing_address[last_name]"]', 'Doe')
                ->type('[name="billing_address[line_1]"]', '123 Main St')
                ->type('[name="billing_address[city]"]', 'New York')
                ->type('[name="billing_address[postal_code]"]', '10001')
                ->select('[name="billing_address[country]"]', 'US')
                ->type('[name="payment[card_number]"]', '4242424242424242')
                ->type('[name="payment[expiry_month]"]', '12')
                ->type('[name="payment[expiry_year]"]', '2025')
                ->type('[name="payment[cvc]"]', '123')
                ->click('[data-place-order-button]')
                ->waitForLocation('/orders/')
                ->assertSee('Order Confirmation')
                ->assertSee('Thank you for your order');
        });
    }

    public function test_customer_can_view_order_history(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit('/account/orders')
                ->assertSee('Order History')
                ->assertSee('No orders found');
        });
    }

    public function test_customer_can_add_product_to_wishlist(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit('/products/'.$this->product->slug)
                ->click('[data-add-to-wishlist-button]')
                ->waitFor('[data-wishlist-notification]')
                ->assertSee('Added to wishlist');
        });
    }

    public function test_customer_can_view_wishlist(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit('/products/'.$this->product->slug)
                ->click('[data-add-to-wishlist-button]')
                ->waitFor('[data-wishlist-notification]')
                ->visit('/account/wishlist')
                ->assertSee('Wishlist')
                ->assertSee('iPhone 15 Pro');
        });
    }

    public function test_customer_can_write_product_review(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->loginAs($this->user)
                ->visit('/products/'.$this->product->slug)
                ->scrollIntoView('[data-reviews-section]')
                ->click('[data-write-review-button]')
                ->waitFor('[data-review-form]')
                ->click('[data-rating="5"]')
                ->type('[name="title"]', 'Great product!')
                ->type('[name="comment"]', 'I love this iPhone, highly recommended.')
                ->click('[data-submit-review-button]')
                ->waitFor('[data-review-success]')
                ->assertSee('Review submitted successfully');
        });
    }

    public function test_product_page_shows_reviews(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('/products/'.$this->product->slug)
                ->scrollIntoView('[data-reviews-section]')
                ->assertSee('Customer Reviews')
                ->assertPresent('[data-average-rating]');
        });
    }

    public function test_customer_can_compare_products(): void
    {
        $secondProduct = Product::factory()->create([
            'name' => 'Samsung Galaxy S24',
            'is_visible' => true,
        ]);

        $this->browse(function (Browser $browser) use ($secondProduct) {
            $browser
                ->visit('/products/'.$this->product->slug)
                ->click('[data-add-to-compare-button]')
                ->waitFor('[data-compare-notification]')
                ->visit('/products/'.$secondProduct->slug)
                ->click('[data-add-to-compare-button]')
                ->waitFor('[data-compare-notification]')
                ->visit('/compare')
                ->assertSee('Product Comparison')
                ->assertSee('iPhone 15 Pro')
                ->assertSee('Samsung Galaxy S24');
        });
    }

    public function test_mobile_responsive_design(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->resize(375, 667)  // iPhone SE size
                ->visit('/')
                ->assertPresent('[data-mobile-menu-toggle]')
                ->click('[data-mobile-menu-toggle]')
                ->waitFor('[data-mobile-menu]')
                ->assertVisible('[data-mobile-menu]')
                ->click('[data-mobile-menu-close]')
                ->waitUntilMissing('[data-mobile-menu]');
        });
    }
}
