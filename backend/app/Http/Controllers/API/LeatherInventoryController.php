<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LeatherInventory;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeatherInventoryController extends Controller
{
    public function index()
    {
        try {
            // TenantModel already handles tenant scoping
            // Use optional relationships to handle null foreign keys gracefully
            $inventory = LeatherInventory::with([
                'supplier' => function($query) {
                    // Supplier extends TenantModel, so it will auto-scope
                },
                'submittedBy',
                'receivedBy'
            ])->get();
            
            return response()->json($inventory);
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
                return response()->json($inventory);
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
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'quantity_sqft' => 'required|numeric|min:0',
            'consumption_reduction' => 'nullable|numeric|min:0',
            'submitted_by' => 'required|exists:users,id',
            'received_by' => 'required|exists:users,id',
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
            'consumption_reduction' => $consumptionReduction,
            'submitted_by' => $request->submitted_by,
            'received_by' => $request->received_by,
            'delivered_to' => $request->delivered_to,
        ]);

        return response()->json($inventory->load(['supplier', 'submittedBy', 'receivedBy']), 201);
    }

    public function show($id)
    {
        $inventory = LeatherInventory::with(['supplier', 'submittedBy', 'receivedBy', 'consumptionLogs'])->findOrFail($id);
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
            'consumption_reduction' => 'nullable|numeric|min:0',
            'delivered_to' => 'nullable|string|max:255',
        ]);

        $inventory->update($request->only([
            'leather_name', 'brand_make', 'supplier_id', 'purchase_date',
            'quantity_sqft', 'consumption_reduction', 'delivered_to'
        ]));

        return response()->json($inventory->load(['supplier', 'submittedBy', 'receivedBy']));
    }
}

