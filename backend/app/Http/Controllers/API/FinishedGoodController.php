<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FinishedGood;
use App\Models\FinishedGoodsAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinishedGoodController extends Controller
{
    public function index()
    {
        $finishedGoods = FinishedGood::where('tenant_id', auth()->user()->tenant_id)
            ->with(['product', 'batch', 'adjustments.adjustedBy'])
            ->get();
        
        return response()->json($finishedGoods);
    }

    public function show($id)
    {
        $finishedGood = FinishedGood::where('tenant_id', auth()->user()->tenant_id)
            ->with(['product', 'batch', 'adjustments.adjustedBy'])
            ->findOrFail($id);
        
        return response()->json($finishedGood);
    }

    public function adjustQuantity(Request $request, $id)
    {
        $request->validate([
            'adjustment_type' => 'required|in:add,deduct',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
            'export_reference' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $finishedGood = FinishedGood::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

            $quantity = $request->quantity;
            if ($request->adjustment_type === 'deduct') {
                if ($quantity > $finishedGood->quantity) {
                    return response()->json([
                        'message' => 'Insufficient quantity. Available: ' . $finishedGood->quantity
                    ], 422);
                }
                // Deduct from quantity
                $finishedGood->decrement('quantity', $quantity);
            } else {
                // Add to quantity
                $finishedGood->increment('quantity', $quantity);
            }

            // Create adjustment log
            $adjustment = FinishedGoodsAdjustment::create([
                'tenant_id' => auth()->user()->tenant_id,
                'finished_good_id' => $finishedGood->id,
                'adjustment_type' => $request->adjustment_type,
                'quantity' => $quantity,
                'reason' => $request->reason,
                'export_reference' => $request->export_reference,
                'adjusted_by' => auth()->id(),
                'adjusted_at' => now(),
            ]);

            return response()->json([
                'finishedGood' => $finishedGood->load(['product', 'batch', 'adjustments.adjustedBy']),
                'adjustment' => $adjustment->load('adjustedBy'),
            ]);
        });
    }
}
