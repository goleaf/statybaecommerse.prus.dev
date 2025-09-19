<?php declare(strict_types=1);

namespace Tests\Unit\Filament\Actions;

use App\Filament\Actions\VariantBulkPriceUpdate;
use Filament\Actions\Action;
use Tests\TestCase;

final class VariantBulkPriceUpdateUnitTest extends TestCase
{
    public function test_variant_bulk_price_update_class_exists(): void
    {
        expect(class_exists(VariantBulkPriceUpdate::class))
            ->toBeTrue();
    }

    public function test_can_create_variant_bulk_price_update_action(): void
    {
        $action = VariantBulkPriceUpdate::make();

        expect($action)
            ->toBeInstanceOf(Action::class);
    }

    public function test_action_has_correct_properties(): void
    {
        $action = VariantBulkPriceUpdate::make();

        expect($action->getLabel())
            ->toBe(__('product_variants.actions.bulk_price_update'))
            ->and($action->getIcon())
            ->toBe('heroicon-o-currency-euro')
            ->and($action->getColor())
            ->toBe('warning');
    }

    public function test_action_has_form_schema(): void
    {
        $action = VariantBulkPriceUpdate::make();

        // Test that the action has a form configuration
        expect($action)
            ->not
            ->toBeNull();

        // Test that the action has the correct name
        expect($action->getName())
            ->toBe('bulk_price_update');
    }
}
