<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        // ProductionStage doesn't have tenant_id, so we return all active stages
        // If tenant-specific stages are needed, add tenant_id to the model
        return response()->json(\App\Models\ProductionStage::where('is_active', true)
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
    Route::apiResource('miscellaneous-costs', App\Http\Controllers\API\MiscellaneousCostController::class);
    
    // Logistics
    Route::apiResource('commercial-invoices', App\Http\Controllers\API\CommercialInvoiceController::class);
    Route::get('/commercial-invoices/{invoice}/pdf', [App\Http\Controllers\API\CommercialInvoiceController::class, 'generatePDF']);
    
    // Finished Goods
    Route::get('/finished-goods', function () {
        $finishedGoods = \App\Models\FinishedGood::where('tenant_id', auth()->user()->tenant_id)
            ->with(['product', 'batch'])
            ->get();
        return response()->json($finishedGoods);
    });
    
    // Role Assignment (GM only)
    Route::post('/users/{user}/assign-role', [App\Http\Controllers\API\UserController::class, 'assignRole'])
        ->middleware('permission:employees.create');
    Route::get('/users', [App\Http\Controllers\API\UserController::class, 'index']);
    Route::get('/roles', function () {
        return response()->json(\App\Models\Role::all());
    });
    
    // Reports
    Route::get('/reports/wip-tracker', function () {
        try {
            // TenantModel global scope will automatically filter by tenant_id
            $batches = \App\Models\Batch::with(['order.product', 'currentStage', 'wipInventories.stage'])->get();
            return response()->json($batches);
        } catch (\Exception $e) {
            Log::error('Error fetching WIP tracker: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);
            return response()->json([
                'error' => 'Failed to fetch WIP tracker data',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    });
    
    Route::get('/reports/inventory-levels', function () {
        try {
            $tenantId = auth()->user()->tenant_id;
            
            // Use DB facade for raw queries to avoid global scope conflicts
            $leather = DB::table('leather_inventory')
                ->where('tenant_id', $tenantId)
                ->selectRaw('leather_name, SUM(quantity_sqft - COALESCE(consumption_reduction, 0)) as available')
                ->groupBy('leather_name')
                ->get();
            
            $accessories = DB::table('accessories_inventory')
                ->where('tenant_id', $tenantId)
                ->selectRaw('name, SUM(quantity) as available')
                ->groupBy('name')
                ->get();
            
            return response()->json([
                'leather' => $leather,
                'accessories' => $accessories,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching inventory levels: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);
            return response()->json([
                'error' => 'Failed to fetch inventory levels',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    });
    
    Route::get('/reports/finished-goods-aging', function () {
        try {
            // TenantModel global scope will automatically filter by tenant_id
            $finishedGoods = \App\Models\FinishedGood::with(['product', 'batch'])
                ->where('completed_at', '<=', now()->subDays(30))
                ->get();
            return response()->json($finishedGoods);
        } catch (\Exception $e) {
            Log::error('Error fetching finished goods aging: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);
            return response()->json([
                'error' => 'Failed to fetch finished goods aging data',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    });
});

