<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccessoriesInventory;
use App\Models\AccessoriesInventoryAdjustment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AccessoriesInventoryController extends Controller
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
            $inventory = AccessoriesInventory::with([
                'submittedBy',
                'receivedBy',
                'adjustments.adjustedBy'
            ])->get();
            
            // Calculate statistics
            $uniqueItems = $inventory->unique('name')->count();
            // Use item's own threshold if set, otherwise use default
            $lowStockCount = $inventory->filter(function ($item) {
                $threshold = $item->low_stock_threshold ?? 500; // Default threshold
                return $item->quantity < $threshold;
            })->unique('name')->count();
            
            $recentImports = AccessoriesInventory::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            
            $stats = [
                'total_items' => $uniqueItems,
                'low_stock_items' => $lowStockCount,
                'recent_imports' => $recentImports,
            ];
            
            return response()->json([
                'inventory' => $inventory,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching accessories inventory: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);
            
            // Fallback: return inventory without relationships
            try {
                $inventory = AccessoriesInventory::get();
                return response()->json([
                    'inventory' => $inventory,
                    'stats' => [
                        'total_items' => $inventory->unique('name')->count(),
                        'low_stock_items' => 0,
                        'recent_imports' => 0,
                    ],
                ]);
            } catch (\Exception $fallbackError) {
                Log::error('Fallback also failed: ' . $fallbackError->getMessage());
                return response()->json([
                    'error' => 'Failed to fetch accessories inventory',
                    'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while fetching inventory',
                    'data' => []
                ], 500);
            }
        }
    }

    public function store(Request $request)
    {
        try {
            $tenantId = auth()->user()->tenant_id;
            
            $request->validate([
                'name' => 'required|string|max:255',
                'quantity' => 'required|numeric|min:0',
                'low_stock_threshold' => 'nullable|numeric|min:0',
                'unit' => 'nullable|string|max:50',
                'import_invoice_number' => 'nullable|string|max:255',
                'file' => 'nullable|file|max:10240', // 10MB max
                'submitted_by' => [
                    'required',
                    'integer',
                    Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                        $query->where('tenant_id', $tenantId);
                    })
                ],
                'received_by' => [
                    'required',
                    'integer',
                    Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                        $query->where('tenant_id', $tenantId);
                    })
                ],
            ]);

            $filePath = null;
            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('accessories_documents', 'public');
            }

            $inventory = AccessoriesInventory::create([
                'tenant_id' => auth()->user()->tenant_id,
                'name' => $request->name,
                'quantity' => (float) $request->quantity,
                'low_stock_threshold' => $request->low_stock_threshold ? (float) $request->low_stock_threshold : null,
                'unit' => $request->unit ?? 'pcs',
                'import_invoice_number' => $request->import_invoice_number,
                'file_path' => $filePath,
                'submitted_by' => (int) $request->submitted_by,
                'received_by' => (int) $request->received_by,
            ]);

            return response()->json($inventory->load(['submittedBy', 'receivedBy']), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating accessories inventory: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->except(['file']),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);

            return response()->json([
                'message' => 'Error creating accessories inventory',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while creating the inventory'
            ], 500);
        }
    }

    public function show($id)
    {
        $inventory = AccessoriesInventory::with([
            'submittedBy', 
            'receivedBy', 
            'consumptionLogs',
            'adjustments.adjustedBy'
        ])->findOrFail($id);
        return response()->json($inventory);
    }

    public function update(Request $request, $id)
    {
        try {
            $inventory = AccessoriesInventory::findOrFail($id);
            $tenantId = auth()->user()->tenant_id;

            $validationRules = [
                'name' => 'sometimes|string|max:255',
                'quantity' => 'sometimes|numeric|min:0',
                'low_stock_threshold' => 'nullable|numeric|min:0',
                'unit' => 'nullable|string|max:50',
                'import_invoice_number' => 'nullable|string|max:255',
                'file' => 'nullable|file|max:10240',
            ];

            // Only validate user fields if they are present in the request
            if ($request->has('submitted_by')) {
                $validationRules['submitted_by'] = [
                    'sometimes',
                    'integer',
                    Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                        $query->where('tenant_id', $tenantId);
                    })
                ];
            }

            if ($request->has('received_by')) {
                $validationRules['received_by'] = [
                    'sometimes',
                    'integer',
                    Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                        $query->where('tenant_id', $tenantId);
                    })
                ];
            }

            $request->validate($validationRules);

            $updateData = [];
            
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            if ($request->has('quantity')) {
                $updateData['quantity'] = (float) $request->quantity;
            }
            if ($request->has('low_stock_threshold')) {
                $updateData['low_stock_threshold'] = $request->low_stock_threshold ? (float) $request->low_stock_threshold : null;
            }
            if ($request->has('unit')) {
                $updateData['unit'] = $request->unit;
            }
            if ($request->has('import_invoice_number')) {
                $updateData['import_invoice_number'] = $request->import_invoice_number;
            }
            if ($request->has('submitted_by') && $request->submitted_by) {
                $updateData['submitted_by'] = (int) $request->submitted_by;
            }
            if ($request->has('received_by') && $request->received_by) {
                $updateData['received_by'] = (int) $request->received_by;
            }

            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($inventory->file_path) {
                    Storage::disk('public')->delete($inventory->file_path);
                }
                $updateData['file_path'] = $request->file('file')->store('accessories_documents', 'public');
            }

            $inventory->update($updateData);

            return response()->json($inventory->fresh()->load(['submittedBy', 'receivedBy', 'adjustments.adjustedBy']));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating accessories inventory: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'inventory_id' => $id,
                'request_data' => $request->except(['file']),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);

            return response()->json([
                'message' => 'Error updating accessories inventory',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while updating the inventory'
            ], 500);
        }
    }

    public function adjustQuantity(Request $request, $id)
    {
        $request->validate([
            'adjustment_type' => 'required|in:add,deduct',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $inventory = AccessoriesInventory::findOrFail($id);

            $quantity = $request->quantity;
            if ($request->adjustment_type === 'deduct') {
                if ($quantity > $inventory->quantity) {
                    return response()->json([
                        'message' => 'Insufficient quantity. Available: ' . $inventory->quantity
                    ], 422);
                }
                // Deduct from quantity
                $inventory->decrement('quantity', $quantity);
            } else {
                // Add to quantity
                $inventory->increment('quantity', $quantity);
            }

            // Create adjustment log
            $adjustment = AccessoriesInventoryAdjustment::create([
                'tenant_id' => auth()->user()->tenant_id,
                'accessory_inventory_id' => $inventory->id,
                'adjustment_type' => $request->adjustment_type,
                'quantity' => $quantity,
                'notes' => $request->notes,
                'adjusted_by' => auth()->id(),
                'adjusted_at' => now(),
            ]);

            return response()->json([
                'inventory' => $inventory->load(['submittedBy', 'receivedBy', 'adjustments.adjustedBy']),
                'adjustment' => $adjustment->load('adjustedBy'),
            ]);
        });
    }
}

