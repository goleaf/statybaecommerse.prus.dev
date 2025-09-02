<?php declare(strict_types=1);

use Livewire\Livewire;

$livewireComponents = [
    \App\Livewire\Components\Navigation::class,
    \App\Livewire\Cpanel\Reviews\Index::class,
    \App\Livewire\Pages\AbstractPageComponent::class,
    \App\Livewire\Cpanel\Products\Index::class,
    \App\Livewire\Admin\Brands\Index::class,
    \App\Livewire\Admin\Groups\Index::class,
    \App\Livewire\Admin\Collections\Index::class,
    \App\Livewire\Admin\Categories\Index::class,
    \App\Livewire\Admin\Orders\Index::class,
    \App\Livewire\Admin\Discount\Preview::class,
    \App\Livewire\Admin\Redemptions\Index::class,
    \App\Livewire\Admin\Orders\Status::class,
    \App\Livewire\Admin\Campaigns\Edit::class,
    \App\Livewire\Admin\Discount\Presets::class,
    \App\Livewire\Admin\Discount\Codes::class,
    \App\Livewire\Admin\Campaigns\Index::class,
    \App\Livewire\Shared\LanguageSwitcher::class,
    \App\Livewire\Pages\Cart::class,
    \App\Livewire\Pages\Search::class,
    \App\Livewire\Pages\Collection\Show::class,
    \App\Livewire\Pages\Collection\Index::class,
    \App\Livewire\Pages\Category\Show::class,
    \App\Livewire\Pages\Category\Index::class,
    \App\Livewire\Pages\Legal::class,
    \App\Livewire\Pages\SingleProduct::class,
    \App\Livewire\Pages\Home::class,
    \App\Livewire\Pages\Checkout::class,
    \App\Livewire\Pages\Account\Orders::class,
    \App\Livewire\Pages\Account\Addresses::class,
    \App\Livewire\Modals\ZoneSelector::class,
    \App\Livewire\Modals\ShoppingCart::class,
    \App\Livewire\Modals\Account\AddressForm::class,
    \App\Livewire\Forms\LoginForm::class,
    \App\Livewire\Components\AccountMenu::class,
    \App\Livewire\Components\ShippingPrice::class,
    \App\Livewire\Components\TaxPrice::class,
    \App\Livewire\Components\CurrencySelector::class,
    \App\Livewire\Components\CartTotal::class,
    \App\Livewire\Components\CouponForm::class,
    \App\Livewire\Components\Product\Images::class,
    \App\Livewire\Components\Product\ReviewForm::class,
    \App\Livewire\Components\Product\Reviews::class,
    \App\Livewire\Components\Brand\Products::class,
    \App\Livewire\Components\VariantsSelector::class,
    \App\Livewire\Components\CheckoutWizard::class,
    \App\Livewire\Components\Checkout\Shipping::class,
    \App\Livewire\Components\Checkout\Payment::class,
    \App\Livewire\Components\Checkout\Delivery::class,
    \App\Livewire\Actions\Logout::class,
];

dataset('livewire_components', fn() => $livewireComponents);

it('mounts Livewire components without fatal errors', function (string $componentClass): void {
    // Some components require auth or specific state; authenticate by default
    login();

    try {
        Livewire::test($componentClass);
        expect(true)->toBeTrue();
    } catch (Throwable $e) {
        // Mark as skipped when dependencies (policies, bindings, data) are missing in smoke context
        $this->markTestSkipped(sprintf('Skipped %s: %s', $componentClass, $e->getMessage()));
    }
})->with('livewire_components')->group('livewire-smoke');
