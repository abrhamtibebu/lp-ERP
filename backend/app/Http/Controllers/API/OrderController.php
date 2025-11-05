<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BatchService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected BatchService $batchService;

    public function __construct(BatchService $batchService)
    {
        $this->batchService = $batchService;
    }

    public function index()
    {
        $orders = Order::with(['product', 'batches.currentStage'])->get();
        
        // Calculate statistics
        $stats = [
            'total' => Order::count(),
            'in_production' => Order::where('status', 'in_production')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'completed' => Order::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
        
        return response()->json([
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'color' => 'nullable|string|max:255',
            'sku' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $order = Order::create([
            'tenant_id' => auth()->user()->tenant_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'color' => $request->color,
            'sku' => $request->sku,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return response()->json($order->load('product'), 201);
    }

    public function show($id)
    {
        $order = Order::with(['product', 'batches.currentStage', 'batches.stageMovements'])->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'sometimes|integer|min:1',
            'color' => 'nullable|string|max:255',
            'sku' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:pending,in_production,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $order->update($request->only([
            'product_id', 'quantity', 'color', 'sku', 'status', 'notes'
        ]));

        return response()->json($order->load('product'));
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function createBatch(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order must be pending to create batch'], 400);
        }

        $batch = $this->batchService->createBatchFromOrder($order);

        $order->update(['status' => 'in_production']);

        return response()->json($batch->load('order', 'currentStage'), 201);
    }
}

