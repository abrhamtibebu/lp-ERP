<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Revenue;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        $query = Revenue::where('tenant_id', auth()->user()->tenant_id)
            ->with('commercialInvoice');

        if ($request->has('month')) {
            $query->whereMonth('revenue_date', $request->month);
        }

        if ($request->has('year')) {
            $query->whereYear('revenue_date', $request->year);
        }

        $revenues = $query->get();
        return response()->json($revenues);
    }

    public function store(Request $request)
    {
        $request->validate([
            'commercial_invoice_id' => 'nullable|exists:commercial_invoices,id',
            'amount' => 'required|numeric|min:0',
            'revenue_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $revenue = Revenue::create([
            'tenant_id' => auth()->user()->tenant_id,
            'commercial_invoice_id' => $request->commercial_invoice_id,
            'amount' => $request->amount,
            'revenue_date' => $request->revenue_date,
            'description' => $request->description,
        ]);

        return response()->json($revenue->load('commercialInvoice'), 201);
    }

    public function show($id)
    {
        $revenue = Revenue::where('tenant_id', auth()->user()->tenant_id)
            ->with('commercialInvoice')
            ->findOrFail($id);
        return response()->json($revenue);
    }

    public function update(Request $request, $id)
    {
        $revenue = Revenue::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'revenue_date' => 'sometimes|date',
            'description' => 'nullable|string',
        ]);

        $revenue->update($request->only(['amount', 'revenue_date', 'description']));

        return response()->json($revenue->load('commercialInvoice'));
    }

    public function destroy($id)
    {
        $revenue = Revenue::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        $revenue->delete();

        return response()->json(['message' => 'Revenue deleted successfully']);
    }
}

