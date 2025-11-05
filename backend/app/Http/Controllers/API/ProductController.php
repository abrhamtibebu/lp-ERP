<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('tenant_id', auth()->user()->tenant_id)
            ->with('productCost')
            ->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'color' => 'nullable|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'weight_kg' => 'nullable|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'consumption_formula' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $product = Product::create([
            'tenant_id' => auth()->user()->tenant_id,
            'product_name' => $request->product_name,
            'color' => $request->color,
            'sku' => $request->sku,
            'weight_kg' => $request->weight_kg,
            'unit_price' => $request->unit_price,
            'consumption_formula' => $request->consumption_formula,
            'description' => $request->description,
        ]);

        return response()->json($product->load('productCost'), 201);
    }

    public function show($id)
    {
        $product = Product::with('productCost', 'orders', 'finishedGoods')->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'product_name' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:255',
            'sku' => 'sometimes|string|max:255|unique:products,sku,' . $id,
            'weight_kg' => 'nullable|numeric|min:0',
            'unit_price' => 'sometimes|numeric|min:0',
            'consumption_formula' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $product->update($request->only([
            'product_name', 'color', 'sku', 'weight_kg', 
            'unit_price', 'consumption_formula', 'description'
        ]));

        return response()->json($product->load('productCost'));
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}

