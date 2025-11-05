<?php

use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::post('/logout', [App\Http\Controllers\API\AuthController::class, 'logout']);
    Route::get('/user', [App\Http\Controllers\API\AuthController::class, 'user']);
    
    // Employees (HR role)
    Route::apiResource('employees', App\Http\Controllers\API\EmployeeController::class)
        ->middleware('permission:employees.create,employees.edit');
    
    // Suppliers
    Route::apiResource('suppliers', App\Http\Controllers\API\SupplierController::class);
    
    // Fixed Assets
    Route::apiResource('fixed-assets', App\Http\Controllers\API\FixedAssetController::class);
    
    // Inventory
    Route::apiResource('leather-inventory', App\Http\Controllers\API\LeatherInventoryController::class);
    Route::apiResource('accessories-inventory', App\Http\Controllers\API\AccessoriesInventoryController::class);
    
    // Products
    Route::apiResource('products', App\Http\Controllers\API\ProductController::class);
    
    // Production
    Route::apiResource('orders', App\Http\Controllers\API\OrderController::class);
    Route::post('/orders/{order}/create-batch', [App\Http\Controllers\API\OrderController::class, 'createBatch']);
    Route::apiResource('batches', App\Http\Controllers\API\BatchController::class);
    Route::post('/batches/{batch}/move-stage', [App\Http\Controllers\API\BatchController::class, 'moveStage']);
    Route::get('/batches/{batch}/wip-status', [App\Http\Controllers\API\BatchController::class, 'wipStatus']);
    
    // Production Stages
    Route::get('/production-stages', function () {
        return response()->json(\App\Models\ProductionStage::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get());
    });
    
    // Finance
    Route::apiResource('product-costs', App\Http\Controllers\API\ProductCostController::class)
        ->middleware('permission:finance.product_cost');
    Route::get('/product-costs/product/{productId}', [App\Http\Controllers\API\ProductCostController::class, 'getCostForProduct']);
    Route::apiResource('expenses', App\Http\Controllers\API\ExpenseController::class);
    Route::apiResource('advances', App\Http\Controllers\API\AdvanceController::class);
    Route::post('/advances/{advance}/approve', [App\Http\Controllers\API\AdvanceController::class, 'approve']);
    Route::post('/advances/{advance}/reject', [App\Http\Controllers\API\AdvanceController::class, 'reject']);
    Route::apiResource('revenues', App\Http\Controllers\API\RevenueController::class);
    
    // Logistics
    Route::apiResource('commercial-invoices', App\Http\Controllers\API\CommercialInvoiceController::class);
    Route::get('/commercial-invoices/{invoice}/pdf', [App\Http\Controllers\API\CommercialInvoiceController::class, 'generatePDF']);
    
    // Reports
    Route::get('/reports/wip-tracker', function () {
        $batches = \App\Models\Batch::with(['order.product', 'currentStage', 'wipInventories.stage'])->get();
        return response()->json($batches);
    });
    
    Route::get('/reports/inventory-levels', function () {
        return response()->json([
            'leather' => \App\Models\LeatherInventory::selectRaw('leather_name, SUM(quantity_sqft - consumption_reduction) as available')
                ->groupBy('leather_name')
                ->get(),
            'accessories' => \App\Models\AccessoriesInventory::selectRaw('name, SUM(quantity) as available')
                ->groupBy('name')
                ->get(),
        ]);
    });
    
    Route::get('/reports/finished-goods-aging', function () {
        return response()->json(
            \App\Models\FinishedGood::with(['product', 'batch'])
                ->where('completed_at', '<=', now()->subDays(30))
                ->get()
        );
    });
});

