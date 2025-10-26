<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\StockReservation;
use App\Models\VariantInventory;
use App\Notifications\InventoryAnomalyDetected;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

final class ReconcileInventoryCommand extends Command
{
    protected $signature = 'inventory:reconcile';

    protected $description = 'Reconcile stock levels and flag anomalies for follow-up.';

    public function handle(): int
    {
        $anomalies = Collection::make();

        $this->expireStaleReservations($anomalies);
        $this->reconcileVariantInventories($anomalies);
        $this->checkProductStockLevels($anomalies);

        if ($anomalies->isNotEmpty()) {
            $this->alert('Inventory anomalies detected:');
            $anomalies->each(fn (string $message) => $this->line(" - {$message}"));

            $recipient = config('mail.from.address');

            if (! empty($recipient)) {
                Notification::route('mail', $recipient)->notify(new InventoryAnomalyDetected($anomalies->values()->all()));
            }
        } else {
            $this->info('Inventory levels look healthy.');
        }

        return self::SUCCESS;
    }

    private function expireStaleReservations(Collection $anomalies): void
    {
        StockReservation::expired()->chunkById(100, function ($reservations) use ($anomalies): void {
            foreach ($reservations as $reservation) {
                $reservation->expire();

                if ($reservation->variant_inventory_id !== null && ($inventory = VariantInventory::query()->find($reservation->variant_inventory_id)) !== null) {
                    $inventory->updateAvailableStock();
                    $anomalies->push(__('Expired reservation released for variant inventory :id', ['id' => $inventory->getKey()]));
                }

                if ($reservation->product_id !== null) {
                    $anomalies->push(__('Expired reservation released for product ID :id', ['id' => $reservation->product_id]));
                }
            }
        });
    }

    private function reconcileVariantInventories(Collection $anomalies): void
    {
        VariantInventory::query()->chunkById(100, function ($inventories) use ($anomalies): void {
            foreach ($inventories as $inventory) {
                $reserved = (int) $inventory->stockReservations()->active()->sum('quantity');

                if ($reserved !== (int) $inventory->reserved) {
                    $inventory->forceFill([
                        'reserved'  => $reserved,
                        'available' => max(0, $inventory->stock - $reserved),
                    ])->save();

                    $anomalies->push(
                        __('Variant inventory :id reserved count corrected (:stored => :actual)', [
                            'id'     => $inventory->getKey(),
                            'stored' => $inventory->reserved,
                            'actual' => $reserved,
                        ])
                    );
                }

                if ($reserved > $inventory->stock) {
                    $anomalies->push(
                        __('Variant inventory :id reserved quantity exceeds stock by :difference units', [
                            'id'         => $inventory->getKey(),
                            'difference' => $reserved - $inventory->stock,
                        ])
                    );
                }
            }
        });
    }

    private function checkProductStockLevels(Collection $anomalies): void
    {
        Product::query()->where('manage_stock', true)->chunkById(100, function ($products) use ($anomalies): void {
            foreach ($products as $product) {
                $reserved = $product->reservedQuantity();

                if ($reserved > $product->stock_quantity) {
                    $anomalies->push(
                        __('Product :name (ID :id) reservations exceed available stock (:reserved / :stock)', [
                            'name'     => $product->name,
                            'id'       => $product->getKey(),
                            'reserved' => $reserved,
                            'stock'    => $product->stock_quantity,
                        ])
                    );
                }
            }
        });
    }
}
