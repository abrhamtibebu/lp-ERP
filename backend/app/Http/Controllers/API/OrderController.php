<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CommercialInvoice;
use App\Models\ProductCost;
use App\Services\BatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    protected BatchService $batchService;

    public function __construct(BatchService $batchService)
    {
        $this->batchService = $batchService;
    }

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $orders = Order::where('tenant_id', $tenantId)
            ->with(['product', 'batches.currentStage'])
            ->get();
        
        // Calculate statistics
        $stats = [
            'total' => Order::where('tenant_id', $tenantId)->count(),
            'in_production' => Order::where('tenant_id', $tenantId)->where('status', 'in_production')->count(),
            'pending' => Order::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'completed' => Order::where('tenant_id', $tenantId)
                ->where('status', 'completed')
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
            'order_type' => 'nullable|string|in:online_order,corporate_order,sample',
            'quantity' => 'required|integer|min:1',
            'color' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Auto-populate color and SKU from product if not provided
        $product = \App\Models\Product::find($request->product_id);
        $color = $request->color ?? $product->color ?? null;
        $sku = $request->sku ?? $product->sku ?? null;

        $order = Order::create([
            'tenant_id' => auth()->user()->tenant_id,
            'product_id' => $request->product_id,
            'order_type' => $request->order_type,
            'quantity' => $request->quantity,
            'color' => $color,
            'sku' => $sku,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        // Auto-create commercial invoice for the order
        $this->createInvoiceFromOrder($order);

        return response()->json($order->load('product', 'commercialInvoices'), 201);
    }

    protected function createInvoiceFromOrder(Order $order)
    {
        // Calculate total amount from product
        $productCost = ProductCost::where('product_id', $order->product_id)
            ->where('tenant_id', $order->tenant_id)
            ->first();
        
        $unitPrice = $order->product->unit_price ?? 0;
        if ($productCost) {
            $unitPrice = $productCost->cost;
        }
        
        $totalAmount = $unitPrice * $order->quantity;
        
        $invoiceNumber = 'INV-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
        
        $productDetails = [
            [
                'product_id' => $order->product_id,
                'product_name' => $order->product->product_name,
                'sku' => $order->sku,
                'color' => $order->color,
                'quantity' => $order->quantity,
                'price' => $unitPrice,
            ]
        ];
        
        CommercialInvoice::create([
            'tenant_id' => $order->tenant_id,
            'order_id' => $order->id,
            'batch_id' => null, // Will be set when batch is created
            'invoice_number' => $invoiceNumber,
            'product_details' => $productDetails,
            'total_amount' => $totalAmount,
            'invoice_date' => now()->toDateString(),
            'notes' => 'Auto-generated from order',
            'created_by' => auth()->id(),
        ]);
    }

    public function show($id)
    {
        $order = Order::where('tenant_id', auth()->user()->tenant_id)
            ->with(['product', 'batches.currentStage', 'batches.stageMovements'])
            ->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = Order::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'order_type' => 'nullable|string|in:online_order,corporate_order,sample',
            'quantity' => 'sometimes|integer|min:1',
            'color' => 'nullable|string|max:255',
            'sku' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:pending,in_production,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $updateData = $request->only([
            'product_id', 'order_type', 'quantity', 'color', 'sku', 'status', 'notes'
        ]);

        // Auto-populate color and SKU from product if product_id changed and fields not provided
        if ($request->has('product_id') && (!$request->has('color') || !$request->has('sku'))) {
            $product = \App\Models\Product::find($request->product_id);
            if ($product) {
                if (!$request->has('color')) {
                    $updateData['color'] = $product->color ?? $order->color;
                }
                if (!$request->has('sku')) {
                    $updateData['sku'] = $product->sku ?? $order->sku;
                }
            }
        }

        $order->update($updateData);

        return response()->json($order->load('product'));
    }

    public function destroy($id)
    {
        $order = Order::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function createBatch(Request $request, $orderId)
    {
        $order = Order::where('tenant_id', auth()->user()->tenant_id)->findOrFail($orderId);

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order must be pending to create batch'], 400);
        }

        $batch = $this->batchService->createBatchFromOrder($order);

        $order->update(['status' => 'in_production']);

        return response()->json($batch->load('order', 'currentStage'), 201);
    }
}

