<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Services\ProductionService;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    protected ProductionService $productionService;

    public function __construct(ProductionService $productionService)
    {
        $this->productionService = $productionService;
    }

    public function index()
    {
        $batches = Batch::with(['order.product', 'currentStage', 'wipInventories.stage'])->get();
        return response()->json($batches);
    }

    public function show($id)
    {
        $batch = Batch::with([
            'order.product',
            'currentStage',
            'stageMovements.fromStage',
            'stageMovements.toStage',
            'stageMovements.supervisor',
            'wipInventories.stage',
            'leatherConsumptionLogs.leatherInventory',
            'accessoriesConsumptionLogs.accessoryInventory'
        ])->findOrFail($id);

        return response()->json($batch);
    }

    public function moveStage(Request $request, $id)
    {
        $request->validate([
            'to_stage_id' => 'required|exists:production_stages,id',
            'quantity' => 'required|integer|min:1',
            'from_stage_id' => 'nullable|exists:production_stages,id',
            'notes' => 'nullable|string',
        ]);

        $batch = Batch::findOrFail($id);

        if ($batch->current_quantity < $request->quantity) {
            return response()->json([
                'message' => 'Insufficient quantity in batch'
            ], 400);
        }

        $movement = $this->productionService->moveBatchToStage(
            $batch,
            $request->to_stage_id,
            $request->quantity,
            auth()->id(),
            $request->from_stage_id,
            $request->notes
        );

        return response()->json($movement->load('fromStage', 'toStage', 'supervisor'), 201);
    }

    public function update(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);

        $request->validate([
            'current_stage_id' => 'sometimes|exists:production_stages,id',
            'status' => 'sometimes|string|in:in_progress,on_hold,completed',
            'current_quantity' => 'sometimes|integer|min:0',
        ]);

        $batch->update($request->only([
            'current_stage_id', 'status', 'current_quantity'
        ]));

        return response()->json($batch->load(['order.product', 'currentStage']));
    }

    public function destroy($id)
    {
        $batch = Batch::findOrFail($id);
        $batch->delete();

        return response()->json(['message' => 'Batch deleted successfully']);
    }

    public function wipStatus($id)
    {
        $batch = Batch::with(['wipInventories.stage', 'order.product'])->findOrFail($id);
        return response()->json($batch);
    }
}

