<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LeatherInventory;
use App\Models\AccessoriesInventory;
use App\Models\Order;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get all system notifications for the authenticated user
     * Returns notifications from different sources:
     * - Low stock alerts (Leather and Accessories)
     * - Pending orders
     * - Pending batches
     * - Other system notifications
     */
    public function index()
    {
        try {
            $tenantId = auth()->user()->tenant_id;
            
            if (!$tenantId) {
                return response()->json(['error' => 'User must be associated with a tenant'], 403);
            }
            
            $notifications = collect();
            
            // 1. Low Stock Alerts (Leather)
            $leatherInventory = LeatherInventory::with(['supplier'])->get();
            $leatherAlerts = $leatherInventory->filter(function ($item) {
                $available = $item->quantity_sqft - ($item->consumption_reduction ?? 0);
                $threshold = $item->low_stock_threshold;
                
                if ($threshold !== null && $threshold > 0 && $available < $threshold) {
                    return true;
                }
                return false;
            })->map(function ($item) {
                $available = $item->quantity_sqft - ($item->consumption_reduction ?? 0);
                $threshold = $item->low_stock_threshold ?? 0;
                
                return [
                    'id' => 'leather-' . $item->id,
                    'type' => 'low_stock',
                    'category' => 'inventory',
                    'subtype' => 'leather',
                    'title' => 'Low Stock Alert',
                    'message' => "{$item->leather_name} is running low",
                    'description' => "Available: " . round($available, 2) . " sq.ft (Threshold: " . round($threshold, 2) . " sq.ft)",
                    'data' => [
                        'id' => $item->id,
                        'name' => $item->leather_name,
                        'brand_make' => $item->brand_make,
                        'supplier' => $item->supplier ? $item->supplier->name : null,
                        'available_quantity' => round($available, 2),
                        'threshold' => round($threshold, 2),
                        'unit' => 'sq.ft',
                    ],
                    'action_url' => '/inventory/leather',
                    'priority' => 'high',
                    'created_at' => now()->toISOString(),
                ];
            });
            
            // 2. Low Stock Alerts (Accessories)
            $accessoriesInventory = AccessoriesInventory::all();
            $accessoriesAlerts = $accessoriesInventory->filter(function ($item) {
                $threshold = $item->low_stock_threshold;
                
                if ($threshold !== null && $threshold > 0 && $item->quantity < $threshold) {
                    return true;
                }
                return false;
            })->map(function ($item) {
                $threshold = $item->low_stock_threshold ?? 0;
                
                return [
                    'id' => 'accessories-' . $item->id,
                    'type' => 'low_stock',
                    'category' => 'inventory',
                    'subtype' => 'accessories',
                    'title' => 'Low Stock Alert',
                    'message' => "{$item->name} is running low",
                    'description' => "Available: " . round($item->quantity, 2) . " " . ($item->unit ?? 'pcs') . " (Threshold: " . round($threshold, 2) . " " . ($item->unit ?? 'pcs') . ")",
                    'data' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'available_quantity' => round($item->quantity, 2),
                        'threshold' => round($threshold, 2),
                        'unit' => $item->unit ?? 'pcs',
                    ],
                    'action_url' => '/inventory/accessories',
                    'priority' => 'high',
                    'created_at' => now()->toISOString(),
                ];
            });
            
            // 3. Pending Orders (if needed)
            // Add pending orders notifications if you want to notify about orders that need attention
            
            // 4. Pending Batches (if needed)
            // Add pending batches notifications if you want to notify about batches that need attention
            
            // Combine all notifications
            $notifications = $notifications
                ->concat($leatherAlerts)
                ->concat($accessoriesAlerts)
                ->sortByDesc(function ($notification) {
                    // Sort by priority (high first) and then by created_at
                    $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
                    return ($priorityOrder[$notification['priority']] ?? 0) * 1000 + strtotime($notification['created_at']);
                })
                ->values();
            
            // Group notifications by category
            $groupedNotifications = [
                'inventory' => $notifications->where('category', 'inventory')->values(),
                'orders' => $notifications->where('category', 'orders')->values(),
                'batches' => $notifications->where('category', 'batches')->values(),
                'system' => $notifications->where('category', 'system')->values(),
            ];
            
            return response()->json([
                'notifications' => $notifications,
                'grouped' => $groupedNotifications,
                'count' => $notifications->count(),
                'unread_count' => $notifications->count(), // All notifications are considered unread for now
                'summary' => [
                    'low_stock' => $leatherAlerts->count() + $accessoriesAlerts->count(),
                    'orders' => 0,
                    'batches' => 0,
                    'system' => 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch notifications',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while fetching notifications',
                'notifications' => [],
                'grouped' => [
                    'inventory' => [],
                    'orders' => [],
                    'batches' => [],
                    'system' => [],
                ],
                'count' => 0,
                'unread_count' => 0,
                'summary' => [
                    'low_stock' => 0,
                    'orders' => 0,
                    'batches' => 0,
                    'system' => 0,
                ],
            ], 500);
        }
    }
}

