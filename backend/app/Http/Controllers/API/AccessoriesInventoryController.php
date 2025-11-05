<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccessoriesInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                'receivedBy'
            ])->get();
            
            // Calculate statistics
            $uniqueItems = $inventory->unique('name')->count();
            $lowStockThreshold = 500; // Threshold for low stock
            $lowStockCount = $inventory->filter(function ($item) use ($lowStockThreshold) {
                return $item->quantity < $lowStockThreshold;
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
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'import_invoice_number' => 'nullable|string|max:255',
            'submitted_by' => 'required|exists:users,id',
            'received_by' => 'required|exists:users,id',
        ]);

        $inventory = AccessoriesInventory::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'quantity' => $request->quantity,
            'unit' => $request->unit ?? 'pcs',
            'import_invoice_number' => $request->import_invoice_number,
            'submitted_by' => $request->submitted_by,
            'received_by' => $request->received_by,
        ]);

        return response()->json($inventory->load(['submittedBy', 'receivedBy']), 201);
    }

    public function show($id)
    {
        $inventory = AccessoriesInventory::with(['submittedBy', 'receivedBy', 'consumptionLogs'])->findOrFail($id);
        return response()->json($inventory);
    }

    public function update(Request $request, $id)
    {
        $inventory = AccessoriesInventory::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'quantity' => 'sometimes|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'import_invoice_number' => 'nullable|string|max:255',
            'submitted_by' => 'sometimes|exists:users,id',
            'received_by' => 'sometimes|exists:users,id',
        ]);

        $inventory->update($request->only([
            'name', 'quantity', 'unit', 'import_invoice_number', 
            'submitted_by', 'received_by'
        ]));

        return response()->json($inventory->load(['submittedBy', 'receivedBy']));
    }
}

