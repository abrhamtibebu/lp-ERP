<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CommercialInvoice;
use App\Models\ProductCost;
use App\Models\InvoiceAttachment;
use App\Models\Revenue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommercialInvoiceController extends Controller
{
    public function index()
    {
        $invoices = CommercialInvoice::where('tenant_id', auth()->user()->tenant_id)
            ->with(['order.product', 'batch', 'createdBy', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate statistics
        $thisMonth = $invoices->filter(function ($invoice) {
            return $invoice->created_at->month == now()->month 
                && $invoice->created_at->year == now()->year;
        });
        
        $totalShipments = $thisMonth->count();
        $totalValue = $thisMonth->sum('total_amount');
        
        $pending = $invoices->filter(function ($invoice) {
            // Check if invoice status is pending
            return !$invoice->order || $invoice->order->status !== 'completed';
        })->count();
        
        $delivered = $invoices->filter(function ($invoice) {
            // Check if invoice is delivered
            return $invoice->order && $invoice->order->status === 'completed';
        })->count();
        
        $stats = [
            'total_shipments' => $totalShipments,
            'total_value' => round($totalValue, 2),
            'pending' => $pending,
            'delivered' => $delivered,
        ];
        
        return response()->json([
            'invoices' => $invoices,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'batch_id' => 'nullable|exists:batches,id',
            'product_details' => 'required|array',
            'invoice_date' => 'required|date',
            'total_amount' => 'sometimes|numeric|min:0',
            'currency' => 'nullable|string|in:USD,ETB',
            'notes' => 'nullable|string',
            'attachments' => 'array',
            'attachments.*' => 'file|max:10240',
        ]);

        // Calculate total amount from product details if not provided
        $totalAmount = $request->total_amount ?? 0;
        if (!$totalAmount) {
            foreach ($request->product_details as $product) {
                if (isset($product['product_id'])) {
                    $productCost = ProductCost::where('product_id', $product['product_id'])
                        ->where('tenant_id', auth()->user()->tenant_id)
                        ->first();
                    
                    if ($productCost) {
                        $totalAmount += $productCost->cost * ($product['quantity'] ?? 1);
                    }
                } elseif (isset($product['price'])) {
                    $totalAmount += $product['price'] * ($product['quantity'] ?? 1);
                }
            }
        }

        $invoiceNumber = 'INV-' . strtoupper(Str::random(8)) . '-' . date('Ymd');

        $invoice = CommercialInvoice::create([
            'tenant_id' => auth()->user()->tenant_id,
            'order_id' => $request->order_id,
            'batch_id' => $request->batch_id,
            'invoice_number' => $invoiceNumber,
            'product_details' => $request->product_details,
            'total_amount' => $totalAmount,
            'currency' => $request->currency ?? 'USD',
            'invoice_date' => $request->invoice_date,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('invoice_attachments', 'public');
                
                InvoiceAttachment::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'commercial_invoice_id' => $invoice->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Auto-create revenue entry if invoice is finalized (you may want to add a status field)
        Revenue::create([
            'tenant_id' => auth()->user()->tenant_id,
            'commercial_invoice_id' => $invoice->id,
            'amount' => $totalAmount,
            'revenue_date' => $request->invoice_date,
            'description' => 'Revenue from invoice ' . $invoiceNumber,
        ]);

        return response()->json($invoice->load(['order.product', 'batch', 'createdBy', 'attachments']), 201);
    }

    public function show($id)
    {
        $invoice = CommercialInvoice::where('tenant_id', auth()->user()->tenant_id)
            ->with([
                'order.product',
                'batch',
                'createdBy',
                'attachments',
                'revenue'
            ])->findOrFail($id);

        return response()->json($invoice);
    }

    public function update(Request $request, $id)
    {
        $invoice = CommercialInvoice::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $request->validate([
            'order_id' => 'sometimes|exists:orders,id',
            'batch_id' => 'nullable|exists:batches,id',
            'product_details' => 'sometimes|array',
            'total_amount' => 'sometimes|numeric|min:0',
            'invoice_date' => 'sometimes|date',
            'notes' => 'nullable|string',
        ]);

        $updateData = $request->only([
            'order_id', 'batch_id', 'product_details', 'total_amount', 'invoice_date', 'notes'
        ]);

        // Recalculate total if product_details changed
        if ($request->has('product_details')) {
            $totalAmount = 0;
            foreach ($request->product_details as $product) {
                if (isset($product['product_id'])) {
                    $productCost = ProductCost::where('product_id', $product['product_id'])
                        ->where('tenant_id', auth()->user()->tenant_id)
                        ->first();
                    
                    if ($productCost) {
                        $totalAmount += $productCost->cost * ($product['quantity'] ?? 1);
                    }
                } elseif (isset($product['price'])) {
                    $totalAmount += $product['price'] * ($product['quantity'] ?? 1);
                }
            }
            $updateData['total_amount'] = $totalAmount;
        }

        $invoice->update($updateData);

        return response()->json($invoice->load(['order.product', 'batch', 'createdBy', 'attachments']));
    }

    public function destroy($id)
    {
        $invoice = CommercialInvoice::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    public function generatePDF($id)
    {
        $invoice = CommercialInvoice::with(['order.product', 'batch'])->findOrFail($id);
        
        // PDF generation would go here using a library like DomPDF or similar
        // For now, return invoice data
        return response()->json([
            'invoice' => $invoice,
            'message' => 'PDF generation would be implemented here'
        ]);
    }
}

