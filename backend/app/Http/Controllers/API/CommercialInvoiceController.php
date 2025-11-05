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
            ->with(['order', 'batch', 'createdBy', 'attachments'])
            ->get();
        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'batch_id' => 'nullable|exists:batches,id',
            'product_details' => 'required|array',
            'invoice_date' => 'required|date',
            'notes' => 'nullable|string',
            'attachments' => 'array',
            'attachments.*' => 'file|max:10240',
        ]);

        // Calculate total amount from product details
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

        $invoiceNumber = 'INV-' . strtoupper(Str::random(8)) . '-' . date('Ymd');

        $invoice = CommercialInvoice::create([
            'tenant_id' => auth()->user()->tenant_id,
            'order_id' => $request->order_id,
            'batch_id' => $request->batch_id,
            'invoice_number' => $invoiceNumber,
            'product_details' => $request->product_details,
            'total_amount' => $totalAmount,
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

        // Auto-create revenue entry
        Revenue::create([
            'tenant_id' => auth()->user()->tenant_id,
            'commercial_invoice_id' => $invoice->id,
            'amount' => $totalAmount,
            'revenue_date' => $request->invoice_date,
            'description' => 'Revenue from invoice ' . $invoiceNumber,
        ]);

        return response()->json($invoice->load(['order', 'batch', 'createdBy', 'attachments']), 201);
    }

    public function show($id)
    {
        $invoice = CommercialInvoice::with([
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
        $invoice = CommercialInvoice::findOrFail($id);

        $request->validate([
            'order_id' => 'sometimes|exists:orders,id',
            'batch_id' => 'nullable|exists:batches,id',
            'product_details' => 'sometimes|array',
            'total_amount' => 'sometimes|numeric|min:0',
            'invoice_date' => 'sometimes|date',
            'notes' => 'nullable|string',
        ]);

        $invoice->update($request->only([
            'order_id', 'batch_id', 'product_details', 'total_amount', 'invoice_date', 'notes'
        ]));

        return response()->json($invoice->load(['order', 'batch', 'createdBy', 'attachments']));
    }

    public function destroy($id)
    {
        $invoice = CommercialInvoice::findOrFail($id);
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

