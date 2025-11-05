<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use Illuminate\Http\Request;

class FixedAssetController extends Controller
{
    public function index()
    {
        $assets = FixedAsset::where('tenant_id', auth()->user()->tenant_id)->get();
        return response()->json($assets);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'purchase_year' => 'nullable|date',
            'depreciation' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $asset = FixedAsset::create([
            'tenant_id' => auth()->user()->tenant_id,
            'description' => $request->description,
            'purchase_year' => $request->purchase_year,
            'depreciation' => $request->depreciation,
            'notes' => $request->notes,
        ]);

        return response()->json($asset, 201);
    }

    public function show($id)
    {
        $asset = FixedAsset::findOrFail($id);
        return response()->json($asset);
    }

    public function update(Request $request, $id)
    {
        $asset = FixedAsset::findOrFail($id);

        $request->validate([
            'description' => 'sometimes|string|max:255',
            'purchase_year' => 'nullable|date',
            'depreciation' => 'sometimes|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $asset->update($request->only(['description', 'purchase_year', 'depreciation', 'notes']));

        return response()->json($asset);
    }

    public function destroy($id)
    {
        $asset = FixedAsset::findOrFail($id);
        $asset->delete();

        return response()->json(['message' => 'Asset deleted successfully']);
    }
}

