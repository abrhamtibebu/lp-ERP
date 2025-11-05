<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MiscellaneousCost;
use Illuminate\Http\Request;

class MiscellaneousCostController extends Controller
{
    public function index()
    {
        $costs = MiscellaneousCost::where('tenant_id', auth()->user()->tenant_id)
            ->get();
        return response()->json($costs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string|in:adjustment,other',
            'notes' => 'nullable|string',
        ]);

        $cost = MiscellaneousCost::create([
            'tenant_id' => auth()->user()->tenant_id,
            'description' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
            'notes' => $request->notes,
        ]);

        return response()->json($cost, 201);
    }

    public function show($id)
    {
        $cost = MiscellaneousCost::findOrFail($id);
        return response()->json($cost);
    }

    public function update(Request $request, $id)
    {
        $cost = MiscellaneousCost::findOrFail($id);

        $request->validate([
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'type' => 'sometimes|string|in:adjustment,other',
            'notes' => 'nullable|string',
        ]);

        $cost->update($request->only(['description', 'amount', 'type', 'notes']));

        return response()->json($cost);
    }

    public function destroy($id)
    {
        $cost = MiscellaneousCost::findOrFail($id);
        $cost->delete();

        return response()->json(['message' => 'Miscellaneous cost deleted successfully']);
    }
}
