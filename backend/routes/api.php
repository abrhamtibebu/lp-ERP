<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// Public routes
Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::post('/logout', [App\Http\Controllers\API\AuthController::class, 'logout']);
    Route::get('/user', [App\Http\Controllers\API\AuthController::class, 'user']);
    Route::put('/user/profile', [App\Http\Controllers\API\AuthController::class, 'updateProfile']);
    Route::put('/user/change-password', [App\Http\Controllers\API\AuthController::class, 'changePassword']);
    
    // Settings
    Route::get('/settings/user', [App\Http\Controllers\API\SettingsController::class, 'getUserSettings']);
    Route::put('/settings/user', [App\Http\Controllers\API\SettingsController::class, 'updateUserSettings']);
    Route::get('/settings/tenant', [App\Http\Controllers\API\SettingsController::class, 'getTenantSettings']);
    Route::put('/settings/tenant', [App\Http\Controllers\API\SettingsController::class, 'updateTenantSettings']);
    Route::get('/settings/export-data', [App\Http\Controllers\API\SettingsController::class, 'exportUserData']);
    
    // Employees (HR role)
    Route::apiResource('employees', App\Http\Controllers\API\EmployeeController::class)
        ->middleware('permission:employees.create,employees.edit');
    
    // Suppliers
    Route::apiResource('suppliers', App\Http\Controllers\API\SupplierController::class);
    // Fetch business info from Ethiopian Trade Bureau API by TIN
    Route::get('/suppliers/fetch-business-info/{tinNumber}', [App\Http\Controllers\API\SupplierController::class, 'fetchBusinessInfoByTin']);
    
    // Fixed Assets
    Route::apiResource('fixed-assets', App\Http\Controllers\API\FixedAssetController::class);
    
    // Notifications
    Route::get('/notifications', [App\Http\Controllers\API\NotificationController::class, 'index']);
    
    // Inventory
    Route::apiResource('leather-inventory', App\Http\Controllers\API\LeatherInventoryController::class);
    Route::post('/leather-inventory/{id}/adjust', [App\Http\Controllers\API\LeatherInventoryController::class, 'adjustQuantity']);
    Route::get('/leather-inventory/low-stock-alerts', [App\Http\Controllers\API\LeatherInventoryController::class, 'lowStockAlerts']);
    Route::apiResource('accessories-inventory', App\Http\Controllers\API\AccessoriesInventoryController::class);
    Route::post('/accessories-inventory/{id}/adjust', [App\Http\Controllers\API\AccessoriesInventoryController::class, 'adjustQuantity']);
    
    // Products - permission checks are in controller, but allow view access
    Route::get('/products', [App\Http\Controllers\API\ProductController::class, 'index']);
    Route::get('/products/{id}', [App\Http\Controllers\API\ProductController::class, 'show']);
    Route::post('/products', [App\Http\Controllers\API\ProductController::class, 'store'])->middleware('permission:products.manage');
    Route::put('/products/{id}', [App\Http\Controllers\API\ProductController::class, 'update'])->middleware('permission:products.manage');
    Route::delete('/products/{id}', [App\Http\Controllers\API\ProductController::class, 'destroy'])->middleware('permission:products.manage');
    
    // Parker Clay Import
    Route::post('/products/import/parker-clay', [App\Http\Controllers\API\ParkerClayImportController::class, 'import'])->middleware('permission:products.manage');
    
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
    
    // Operations - Procurement
    Route::apiResource('procurement-requests', App\Http\Controllers\API\ProcurementController::class);
    Route::post('/procurement-requests/{id}/approve', [App\Http\Controllers\API\ProcurementController::class, 'approve']);
    Route::post('/procurement-requests/{id}/reject', [App\Http\Controllers\API\ProcurementController::class, 'reject']);
    
    // Finished Goods
    Route::get('/finished-goods', [App\Http\Controllers\API\FinishedGoodController::class, 'index']);
    Route::get('/finished-goods/{id}', [App\Http\Controllers\API\FinishedGoodController::class, 'show']);
    Route::post('/finished-goods/{id}/adjust', [App\Http\Controllers\API\FinishedGoodController::class, 'adjustQuantity']);
    
    // Role Management (Admin only)
    Route::apiResource('roles', App\Http\Controllers\API\RoleController::class)
        ->middleware('permission:employees.create');
    
    // Role Assignment (Admin only)
    Route::post('/users/{user}/assign-role', [App\Http\Controllers\API\UserController::class, 'assignRole'])
        ->middleware('permission:employees.create');
    Route::put('/users/{user}/change-password', [App\Http\Controllers\API\UserController::class, 'changePassword'])
        ->middleware('permission:employees.create');
    Route::put('/users/{user}/approver-access', [App\Http\Controllers\API\UserController::class, 'updateApproverAccess'])
        ->middleware('permission:employees.create');
    Route::get('/users', [App\Http\Controllers\API\UserController::class, 'index']);
    
    // Permissions (read-only for Admin)
    Route::get('/permissions', function () {
        try {
            if (!auth()->user()->hasRole('Admin')) {
                return response()->json(['message' => 'Only Admin can view permissions'], 403);
            }
            $permissions = \App\Models\Permission::orderBy('module')->orderBy('display_name')->get();
            $permissionsByModule = $permissions->groupBy('module');
            return response()->json([
                'permissions' => $permissions,
                'permissions_by_module' => $permissionsByModule,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch permissions',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    })->middleware('permission:employees.create');
    
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
    
    // Real-time reporting endpoints with time-based trends
    Route::get('/reports/inventory-trends', function (Request $request) {
        try {
            $tenantId = auth()->user()->tenant_id;
            $days = $request->get('days', 7); // Default to 7 days
            $startDate = now()->subDays($days)->startOfDay();
            
            // Get inventory trends over time
            $leatherTrends = DB::table('leather_inventory_adjustments')
                ->join('leather_inventory', 'leather_inventory_adjustments.leather_inventory_id', '=', 'leather_inventory.id')
                ->where('leather_inventory.tenant_id', $tenantId)
                ->where('leather_inventory_adjustments.adjusted_at', '>=', $startDate)
                ->selectRaw('DATE(leather_inventory_adjustments.adjusted_at) as date, 
                    SUM(CASE WHEN leather_inventory_adjustments.adjustment_type = "add" THEN leather_inventory_adjustments.quantity ELSE -leather_inventory_adjustments.quantity END) as net_change')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            $accessoriesTrends = DB::table('accessories_inventory_adjustments')
                ->join('accessories_inventory', 'accessories_inventory_adjustments.accessory_inventory_id', '=', 'accessories_inventory.id')
                ->where('accessories_inventory.tenant_id', $tenantId)
                ->where('accessories_inventory_adjustments.adjusted_at', '>=', $startDate)
                ->selectRaw('DATE(accessories_inventory_adjustments.adjusted_at) as date, 
                    SUM(CASE WHEN accessories_inventory_adjustments.adjustment_type = "add" THEN accessories_inventory_adjustments.quantity ELSE -accessories_inventory_adjustments.quantity END) as net_change')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // Get order trends
            $orderTrends = DB::table('orders')
                ->where('tenant_id', $tenantId)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(quantity) as total_quantity')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // Get batch completion trends
            $batchTrends = DB::table('batches')
                ->where('tenant_id', $tenantId)
                ->where('updated_at', '>=', $startDate)
                ->where('status', 'completed')
                ->selectRaw('DATE(updated_at) as date, COUNT(*) as completed_count')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            return response()->json([
                'leather_trends' => $leatherTrends,
                'accessories_trends' => $accessoriesTrends,
                'order_trends' => $orderTrends,
                'batch_trends' => $batchTrends,
                'period_days' => $days,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching inventory trends: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch inventory trends',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    });
    
    Route::get('/reports/production-trends', function (Request $request) {
        try {
            $tenantId = auth()->user()->tenant_id;
            $days = $request->get('days', 7);
            $startDate = now()->subDays($days)->startOfDay();
            
            // Get production stage movement trends
            $stageTrends = DB::table('batch_stage_movements')
                ->join('batches', 'batch_stage_movements.batch_id', '=', 'batches.id')
                ->join('production_stages', 'batch_stage_movements.stage_id', '=', 'production_stages.id')
                ->where('batches.tenant_id', $tenantId)
                ->where('batch_stage_movements.created_at', '>=', $startDate)
                ->selectRaw('DATE(batch_stage_movements.created_at) as date, 
                    production_stages.name as stage_name, 
                    COUNT(*) as movement_count')
                ->groupBy('date', 'stage_name')
                ->orderBy('date')
                ->get();
            
            return response()->json([
                'stage_trends' => $stageTrends,
                'period_days' => $days,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching production trends: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch production trends',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    });
});

