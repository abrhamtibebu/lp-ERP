<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\LeatherInventory;
use App\Models\AccessoriesInventory;
use App\Models\LeatherConsumptionLog;
use App\Models\AccessoriesConsumptionLog;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ConsumptionService
{
    public function deductMaterials(Batch $batch, int $quantity, string $consumptionMode): void
    {
        DB::transaction(function () use ($batch, $quantity, $consumptionMode) {
            $product = Product::findOrFail($batch->order->product_id);

            // Leather consumption
            if ($consumptionMode === 'formula' && $product->consumption_formula) {
                $leatherQuantity = $this->calculateLeatherConsumption($product, $quantity);
            } else {
                // Manual or hybrid mode - will be set manually
                $leatherQuantity = 0;
            }

            if ($leatherQuantity > 0) {
                $this->deductLeather($batch, $leatherQuantity, $consumptionMode);
            }

            // Accessories consumption (typically manual)
            // This would be handled separately via API
        });
    }

    public function deductLeather(Batch $batch, float $quantitySqft, string $consumptionType): void
    {
        // Find available leather inventory (FIFO)
        $availableLeather = LeatherInventory::where('tenant_id', $batch->tenant_id)
            ->whereRaw('quantity_sqft - consumption_reduction > 0')
            ->orderBy('purchase_date')
            ->get();

        $remainingQuantity = $quantitySqft;

        foreach ($availableLeather as $leather) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $available = $leather->quantity_sqft - $leather->consumption_reduction;
            $consumed = min($available, $remainingQuantity);

            // Update inventory
            $leather->increment('consumption_reduction', $consumed);

            // Log consumption
            LeatherConsumptionLog::create([
                'tenant_id' => $batch->tenant_id,
                'batch_id' => $batch->id,
                'leather_inventory_id' => $leather->id,
                'quantity_consumed' => $consumed,
                'consumption_type' => $consumptionType,
            ]);

            $remainingQuantity -= $consumed;
        }

        if ($remainingQuantity > 0) {
            throw new \Exception("Insufficient leather inventory. Need {$remainingQuantity} more sqft.");
        }
    }

    public function deductAccessories(Batch $batch, int $accessoryId, float $quantity): void
    {
        $accessory = AccessoriesInventory::findOrFail($accessoryId);

        if ($accessory->quantity < $quantity) {
            throw new \Exception("Insufficient accessory inventory. Available: {$accessory->quantity}, Required: {$quantity}");
        }

        $accessory->decrement('quantity', $quantity);

        AccessoriesConsumptionLog::create([
            'tenant_id' => $batch->tenant_id,
            'batch_id' => $batch->id,
            'accessory_inventory_id' => $accessoryId,
            'quantity_consumed' => $quantity,
        ]);
    }

    protected function calculateLeatherConsumption(Product $product, int $quantity): float
    {
        // Simple formula calculation - can be enhanced
        // For now, assuming formula is stored as "sqft_per_unit" or similar
        if (preg_match('/(\d+\.?\d*)/', $product->consumption_formula, $matches)) {
            $sqftPerUnit = (float) $matches[1];
            return $sqftPerUnit * $quantity;
        }

        return 0;
    }
}

