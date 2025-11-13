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

    public function index(Request $request)
    {
        try {
            // By default, show only active batches (not completed)
            // Allow ?include_completed=true to show all batches
            $query = Batch::with(['order.product', 'currentStage', 'wipInventories.stage', 'stageMovements']);
            
            if (!$request->has('include_completed') || !$request->boolean('include_completed')) {
                // Filter out completed batches - show only opened/active batches
                // Use whereNotIn for enum fields to be safe
                $query->whereNotIn('status', ['completed']);
            }
            
            $batches = $query->get();
            
            // Get all production stages ordered
            $allStages = \App\Models\ProductionStage::orderBy('order')->get();
            
            // Calculate stage progress for each batch
            $batches = $batches->map(function ($batch) use ($allStages) {
                $stageProgress = [];
                $totalQuantity = $batch->total_quantity;
                
                foreach ($allStages as $stage) {
                    // Units that have completed this stage = units that moved FROM this stage
                    // This counts how many units have passed through and moved to the next stage
                    $completedUnits = $batch->stageMovements()
                        ->where('from_stage_id', $stage->id)
                        ->sum('quantity');
                    
                    // Units that have entered this stage = units that moved TO this stage
                    $enteredUnits = $batch->stageMovements()
                        ->where('to_stage_id', $stage->id)
                        ->sum('quantity');
                    
                    // Get current WIP units at this stage (units currently being processed)
                    $wipUnits = $batch->wipInventories()
                        ->where('stage_id', $stage->id)
                        ->sum('quantity');
                    
                    // For display: show completed units (units that have passed through)
                    // The frontend will add WIP for the current stage
                    $totalReached = $completedUnits;
                    
                    $stageProgress[] = [
                        'id' => $stage->id,
                        'name' => $stage->name,
                        'order' => $stage->order,
                        'completed_units' => (int) $completedUnits,
                        'entered_units' => (int) $enteredUnits,
                        'wip_units' => (int) $wipUnits,
                        'total_reached' => (int) $totalReached,
                        'total_quantity' => $totalQuantity,
                    ];
                }
                
                // Calculate overall progress based on main stages
                $stagesToCheck = ['Cutting', 'Schiving', 'Initial Stitching', 'Final Assembly', 'Quality Inspection'];
                $completedStages = 0;
                foreach ($stageProgress as $progress) {
                    if (in_array($progress['name'], $stagesToCheck)) {
                        // A stage is "completed" when all units have passed through it
                        if ($progress['completed_units'] >= $totalQuantity) {
                            $completedStages++;
                        }
                    }
                }
                $overallProgress = count($stagesToCheck) > 0 ? ($completedStages / count($stagesToCheck)) * 100 : 0;
                
                $batch->stage_progress = $stageProgress;
                $batch->overall_progress = round($overallProgress, 1);
                
                return $batch;
            });
            
            return response()->json($batches);
        } catch (\Exception $e) {
            \Log::error('Error fetching batches: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);
            return response()->json([
                'error' => 'Failed to fetch batches',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
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
        try {
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
        } catch (\Exception $e) {
            \Log::error('Error moving batch to stage: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'batch_id' => $id,
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);
            return response()->json([
                'error' => 'Failed to move batch to stage',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
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

