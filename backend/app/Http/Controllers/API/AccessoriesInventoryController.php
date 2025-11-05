<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccessoriesInventory;
use Illuminate\Http\Request;

class AccessoriesInventoryController extends Controller
{
    public function index()
    {
        $inventory = AccessoriesInventory::where('tenant_id', auth()->user()->tenant_id)
            ->with(['submittedBy', 'receivedBy'])
            ->get();
        return response()->json($inventory);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'import_invoice_number' => 'nullable|string|max:255',
            'submitted_by' => 'required|exists:users,id',
            'received_by' => 'required|exists:users,id',
        ]);

        $inventory = AccessoriesInventory::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'quantity' => $request->quantity,
            'unit' => $request->unit ?? 'pcs',
            'import_invoice_number' => $request->import_invoice_number,
            'submitted_by' => $request->submitted_by,
            'received_by' => $request->received_by,
        ]);

        return response()->json($inventory->load(['submittedBy', 'receivedBy']), 201);
    }

    public function show($id)
    {
        $inventory = AccessoriesInventory::with(['submittedBy', 'receivedBy', 'consumptionLogs'])->findOrFail($id);
        return response()->json($inventory);
    }

    public function update(Request $request, $id)
    {
        $inventory = AccessoriesInventory::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'quantity' => 'sometimes|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'import_invoice_number' => 'nullable|string|max:255',
        ]);

        $inventory->update($request->only(['name', 'quantity', 'unit', 'import_invoice_number']));

        return response()->json($inventory->load(['submittedBy', 'receivedBy']));
    }
}

