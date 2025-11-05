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
        
        // Calculate statistics
        // Note: For accurate calculations, we'd need purchase_value field
        // For now, using simplified estimates
        $totalAssets = $assets->count();
        
        // Estimate values based on depreciation (assuming average purchase value)
        $totalCurrentValue = 0;
        $totalPurchaseValue = 0;
        
        foreach ($assets as $asset) {
            if ($asset->purchase_year && $asset->depreciation) {
                $yearsSincePurchase = now()->year - (new \DateTime($asset->purchase_year))->format('Y');
                // Estimate purchase value (in real system, this would be stored)
                $estimatedPurchase = 50000;
                $totalPurchaseValue += $estimatedPurchase;
                
                // Calculate current value: purchase * (1 - depreciation_rate)^years
                $depreciationRate = $asset->depreciation / 100;
                $currentValue = $estimatedPurchase * pow(1 - $depreciationRate, max($yearsSincePurchase, 0));
                $totalCurrentValue += max($currentValue, 0);
            } else {
                $estimatedPurchase = 50000;
                $totalPurchaseValue += $estimatedPurchase;
                $totalCurrentValue += $estimatedPurchase;
            }
        }
        
        $totalDepreciation = $totalPurchaseValue - $totalCurrentValue;
        $activeAssets = $totalAssets;
        
        $stats = [
            'total_assets' => $totalAssets,
            'current_value' => round($totalCurrentValue, 2),
            'total_depreciation' => round(max($totalDepreciation, 0), 2),
            'active_assets' => $activeAssets,
        ];
        
        return response()->json([
            'assets' => $assets,
            'stats' => $stats,
        ]);
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

