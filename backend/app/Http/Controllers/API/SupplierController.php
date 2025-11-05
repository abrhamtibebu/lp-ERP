<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::where('tenant_id', auth()->user()->tenant_id)
            ->with('leatherInventories')
            ->get();
        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tin_number' => 'nullable|string|max:255',
            'products_supplied' => 'nullable|string',
            'contact_info' => 'nullable|string',
        ]);

        $supplier = Supplier::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'tin_number' => $request->tin_number,
            'products_supplied' => $request->products_supplied,
            'contact_info' => $request->contact_info,
        ]);

        return response()->json($supplier, 201);
    }

    public function show($id)
    {
        $supplier = Supplier::with('leatherInventories')->findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'tin_number' => 'nullable|string|max:255',
            'products_supplied' => 'nullable|string',
            'contact_info' => 'nullable|string',
        ]);

        $supplier->update($request->only(['name', 'tin_number', 'products_supplied', 'contact_info']));

        return response()->json($supplier);
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully']);
    }
}

