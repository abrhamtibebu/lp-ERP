<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LeatherInventory;
use App\Models\LeatherInventoryAdjustment;
use App\Models\AccessoriesInventory;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeatherInventoryController extends Controller
{
    public function index()
    {
        try {
            $tenantId = auth()->user()->tenant_id;
            
            if (!$tenantId) {
                return response()->json(['error' => 'User must be associated with a tenant'], 403);
            }
            
            // TenantModel global scope already filters by tenant_id
            // Just load relationships normally - foreign keys ensure correct data
            $inventory = LeatherInventory::with([
                'supplier',
                'submittedBy',
                'receivedBy',
                'adjustments.adjustedBy'
            ])->get();
            
            // Calculate statistics
            $totalStock = $inventory->sum(function ($item) {
                return $item->quantity_sqft - ($item->consumption_reduction ?? 0);
            });
            
            $uniqueTypes = $inventory->unique('leather_name')->count();
            // Use item's own threshold if set, otherwise use default
            $lowStockCount = $inventory->filter(function ($item) {
                $available = $item->quantity_sqft - ($item->consumption_reduction ?? 0);
                $threshold = $item->low_stock_threshold ?? 500; // Default threshold
                return $available < $threshold;
            })->unique('leather_name')->count();
            
            $activeSuppliers = $inventory->pluck('supplier_id')->filter()->unique()->count();
            
            $stats = [
                'total_stock' => round($totalStock, 2),
                'unique_types' => $uniqueTypes,
                'low_stock_items' => $lowStockCount,
                'active_suppliers' => $activeSuppliers,
            ];
            
            return response()->json([
                'inventory' => $inventory,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching leather inventory: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);
            
            // Fallback: return inventory without relationships
            try {
                $inventory = LeatherInventory::get();
                return response()->json([
                    'inventory' => $inventory,
                    'stats' => [
                        'total_stock' => $inventory->sum('quantity_sqft'),
                        'unique_types' => $inventory->unique('leather_name')->count(),
                        'low_stock_items' => 0,
                        'active_suppliers' => 0,
                    ],
                ]);
            } catch (\Exception $fallbackError) {
                Log::error('Fallback also failed: ' . $fallbackError->getMessage());
                return response()->json([
                    'error' => 'Failed to fetch leather inventory',
                    'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while fetching inventory',
                    'data' => []
                ], 500);
            }
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'leather_name' => 'required|string|max:255',
            'brand_make' => 'nullable|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'quantity_sqft' => 'required|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'consumption_reduction' => 'nullable|numeric|min:0',
            'submitted_by' => 'nullable|exists:users,id',
            'received_by' => 'nullable|exists:users,id',
            'delivered_to' => 'nullable|string|max:255',
        ]);

        $tenant = Tenant::findOrFail(auth()->user()->tenant_id);
        
        // Auto-calculate consumption reduction if tenant uses formula mode
        $consumptionReduction = $request->consumption_reduction ?? 0;
        if ($tenant->leather_consumption_mode === 'formula' && $request->has('consumption_formula')) {
            // Formula calculation would go here
            // For now, we'll use manual entry
        }

        $inventory = LeatherInventory::create([
            'tenant_id' => auth()->user()->tenant_id,
            'leather_name' => $request->leather_name,
            'brand_make' => $request->brand_make,
            'supplier_id' => $request->supplier_id,
            'purchase_date' => $request->purchase_date,
            'quantity_sqft' => $request->quantity_sqft,
            'low_stock_threshold' => $request->low_stock_threshold,
            'consumption_reduction' => $consumptionReduction,
            'submitted_by' => $request->submitted_by,
            'received_by' => $request->received_by,
            'delivered_to' => $request->delivered_to,
        ]);

        return response()->json($inventory->load(['supplier', 'submittedBy', 'receivedBy']), 201);
    }

    public function show($id)
    {
        $inventory = LeatherInventory::with([
            'supplier', 
            'submittedBy', 
            'receivedBy', 
            'consumptionLogs',
            'adjustments.adjustedBy'
        ])->findOrFail($id);
        return response()->json($inventory);
    }

    public function update(Request $request, $id)
    {
        $inventory = LeatherInventory::findOrFail($id);

        $request->validate([
            'leather_name' => 'sometimes|string|max:255',
            'brand_make' => 'nullable|string|max:255',
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'purchase_date' => 'sometimes|date',
            'quantity_sqft' => 'sometimes|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'consumption_reduction' => 'nullable|numeric|min:0',
            'delivered_to' => 'nullable|string|max:255',
        ]);

        $inventory->update($request->only([
            'leather_name', 'brand_make', 'supplier_id', 'purchase_date',
            'quantity_sqft', 'low_stock_threshold', 'consumption_reduction', 'delivered_to'
        ]));

        return response()->json($inventory->load(['supplier', 'submittedBy', 'receivedBy', 'adjustments.adjustedBy']));
    }

    public function adjustQuantity(Request $request, $id)
    {
        $request->validate([
            'adjustment_type' => 'required|in:add,deduct',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $inventory = LeatherInventory::findOrFail($id);

            $quantity = $request->quantity;
            if ($request->adjustment_type === 'deduct') {
                $available = $inventory->quantity_sqft - ($inventory->consumption_reduction ?? 0);
                if ($quantity > $available) {
                    return response()->json([
                        'message' => 'Insufficient quantity. Available: ' . $available . ' sqft'
                    ], 422);
                }
                // Deduct from quantity_sqft
                $inventory->increment('quantity_sqft', -$quantity);
            } else {
                // Add to quantity_sqft
                $inventory->increment('quantity_sqft', $quantity);
            }

            // Create adjustment log
            $adjustment = LeatherInventoryAdjustment::create([
                'tenant_id' => auth()->user()->tenant_id,
                'leather_inventory_id' => $inventory->id,
                'adjustment_type' => $request->adjustment_type,
                'quantity' => $quantity,
                'notes' => $request->notes,
                'adjusted_by' => auth()->id(),
                'adjusted_at' => now(),
            ]);

            return response()->json([
                'inventory' => $inventory->load(['supplier', 'submittedBy', 'receivedBy', 'adjustments.adjustedBy']),
                'adjustment' => $adjustment->load('adjustedBy'),
            ]);
        });
    }

    /**
     * Get low stock alerts for all inventory items (Leather and Accessories)
     * Returns items where available quantity is below the threshold
     */
    public function lowStockAlerts()
    {
        try {
            $tenantId = auth()->user()->tenant_id;
            
            if (!$tenantId) {
                return response()->json(['error' => 'User must be associated with a tenant'], 403);
            }
            
            $allAlerts = collect();
            
            // Get leather inventory items with low stock
            $leatherInventory = LeatherInventory::with([
                'supplier',
                'submittedBy',
                'receivedBy'
            ])->get();
            
            // Filter leather items that are below their threshold
            $leatherAlerts = $leatherInventory->filter(function ($item) {
                $available = $item->quantity_sqft - ($item->consumption_reduction ?? 0);
                $threshold = $item->low_stock_threshold;
                
                // Only include items that have a threshold set and are below it
                if ($threshold !== null && $threshold > 0) {
                    return $available < $threshold;
                }
                
                return false;
            })->map(function ($item) {
                $available = $item->quantity_sqft - ($item->consumption_reduction ?? 0);
                $threshold = $item->low_stock_threshold ?? 0;
                
                return [
                    'id' => $item->id,
                    'type' => 'leather',
                    'name' => $item->leather_name,
                    'brand_make' => $item->brand_make,
                    'supplier' => $item->supplier ? $item->supplier->name : null,
                    'available_quantity' => round($available, 2),
                    'threshold' => round($threshold, 2),
                    'unit' => 'sq.ft',
                    'purchase_date' => $item->purchase_date,
                ];
            });
            
            // Get accessories inventory items with low stock
            $accessoriesInventory = AccessoriesInventory::with([
                'submittedBy',
                'receivedBy'
            ])->get();
            
            // Filter accessories items that are below their threshold
            $accessoriesAlerts = $accessoriesInventory->filter(function ($item) {
                $threshold = $item->low_stock_threshold;
                
                // Only include items that have a threshold set and are below it
                if ($threshold !== null && $threshold > 0) {
                    return $item->quantity < $threshold;
                }
                
                return false;
            })->map(function ($item) {
                $threshold = $item->low_stock_threshold ?? 0;
                
                return [
                    'id' => $item->id,
                    'type' => 'accessories',
                    'name' => $item->name,
                    'brand_make' => null,
                    'supplier' => null,
                    'available_quantity' => round($item->quantity, 2),
                    'threshold' => round($threshold, 2),
                    'unit' => $item->unit ?? 'pcs',
                    'purchase_date' => null,
                ];
            });
            
            // Combine all alerts and sort by available quantity (lowest first)
            $allAlerts = $leatherAlerts->concat($accessoriesAlerts)->sortBy('available_quantity')->values();
            
            return response()->json([
                'alerts' => $allAlerts,
                'count' => $allAlerts->count(),
                'leather_count' => $leatherAlerts->count(),
                'accessories_count' => $accessoriesAlerts->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching low stock alerts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch low stock alerts',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while fetching alerts',
                'alerts' => [],
                'count' => 0,
                'leather_count' => 0,
                'accessories_count' => 0,
            ], 500);
        }
    }
}

