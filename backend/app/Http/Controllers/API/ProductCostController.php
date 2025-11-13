<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductCost;
use Illuminate\Http\Request;

class ProductCostController extends Controller
{
    public function index()
    {
        $costs = ProductCost::where('tenant_id', auth()->user()->tenant_id)
            ->with(['product', 'lockedBy'])
            ->get();
        return response()->json($costs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'cost' => 'required|numeric|min:0',
            'currency' => 'nullable|string|in:USD,ETB',
            'notes' => 'nullable|string',
        ]);

        $cost = ProductCost::updateOrCreate(
            [
                'tenant_id' => auth()->user()->tenant_id,
                'product_id' => $request->product_id,
            ],
            [
                'cost' => $request->cost,
                'currency' => $request->currency ?? 'USD',
                'is_locked' => true,
                'locked_by' => auth()->id(),
                'locked_at' => now(),
                'notes' => $request->notes,
            ]
        );

        return response()->json($cost->load('product', 'lockedBy'), 201);
    }

    public function show($id)
    {
        $cost = ProductCost::with(['product', 'lockedBy'])->findOrFail($id);
        return response()->json($cost);
    }

    public function update(Request $request, $id)
    {
        $cost = ProductCost::findOrFail($id);

        $request->validate([
            'cost' => 'sometimes|numeric|min:0',
            'currency' => 'nullable|string|in:USD,ETB',
            'notes' => 'nullable|string',
        ]);

        $cost->update([
            'cost' => $request->cost ?? $cost->cost,
            'currency' => $request->currency ?? $cost->currency ?? 'USD',
            'locked_by' => auth()->id(),
            'locked_at' => now(),
            'notes' => $request->notes ?? $cost->notes,
        ]);

        return response()->json($cost->load('product', 'lockedBy'));
    }

    public function getCostForProduct($productId)
    {
        $cost = ProductCost::where('product_id', $productId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$cost) {
            return response()->json(['message' => 'Product cost not found'], 404);
        }

        return response()->json($cost);
    }
}

