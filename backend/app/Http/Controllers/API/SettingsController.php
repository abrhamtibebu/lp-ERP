<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    /**
     * Get user settings/preferences
     */
    public function getUserSettings(Request $request)
    {
        $user = $request->user();
        
        // Default preferences
        $defaultPreferences = [
            'theme' => 'light',
            'language' => 'en',
            'email_notifications' => true,
            'order_notifications' => true,
            'batch_notifications' => true,
            'auto_refresh' => 60, // seconds
            'items_per_page' => 25,
        ];

        $preferences = $user->preferences ?? [];
        $preferences = array_merge($defaultPreferences, $preferences);

        return response()->json([
            'preferences' => $preferences,
        ]);
    }

    /**
     * Update user settings/preferences
     */
    public function updateUserSettings(Request $request)
    {
        $request->validate([
            'theme' => 'nullable|in:light,dark,system',
            'language' => 'nullable|string|max:10',
            'email_notifications' => 'nullable|boolean',
            'order_notifications' => 'nullable|boolean',
            'batch_notifications' => 'nullable|boolean',
            'auto_refresh' => 'nullable|integer|min:0',
            'items_per_page' => 'nullable|integer|in:10,25,50,100',
        ]);

        $user = $request->user();
        
        // Get existing preferences or initialize
        $preferences = $user->preferences ?? [];
        
        // Merge with new preferences
        $preferences = array_merge($preferences, $request->only([
            'theme',
            'language',
            'email_notifications',
            'order_notifications',
            'batch_notifications',
            'auto_refresh',
            'items_per_page',
        ]));

        // Remove null values
        $preferences = array_filter($preferences, function ($value) {
            return $value !== null;
        });

        $user->preferences = $preferences;
        $user->save();

        return response()->json([
            'message' => 'Settings updated successfully',
            'preferences' => $preferences,
        ]);
    }

    /**
     * Get tenant settings (only for Admin role)
     */
    public function getTenantSettings(Request $request)
    {
        $user = $request->user();
        
        // Check if user has permission to view tenant settings (Admin role)
        if (!$user->hasRole('Admin')) {
            return response()->json([
                'message' => 'Unauthorized. Only Admin can view tenant settings.',
            ], 403);
        }

        $tenant = $user->tenant;
        
        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found',
            ], 404);
        }

        // Default tenant settings
        $defaultSettings = [
            'low_stock_threshold_leather' => 100, // sqft
            'low_stock_threshold_accessories' => 50,
            'auto_generate_batch_id' => true,
            'auto_create_invoice' => true,
            'notification_email' => null,
        ];

        $settings = $tenant->settings ?? [];
        $settings = array_merge($defaultSettings, $settings);

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'leather_consumption_mode' => $tenant->leather_consumption_mode,
            ],
            'settings' => $settings,
        ]);
    }

    /**
     * Update tenant settings (only for Admin role)
     */
    public function updateTenantSettings(Request $request)
    {
        $user = $request->user();
        
        // Check if user has permission to update tenant settings (Admin role)
        if (!$user->hasRole('Admin')) {
            return response()->json([
                'message' => 'Unauthorized. Only Admin can update tenant settings.',
            ], 403);
        }

        $request->validate([
            'leather_consumption_mode' => 'nullable|in:formula,manual,hybrid',
            'low_stock_threshold_leather' => 'nullable|integer|min:0',
            'low_stock_threshold_accessories' => 'nullable|integer|min:0',
            'auto_generate_batch_id' => 'nullable|boolean',
            'auto_create_invoice' => 'nullable|boolean',
            'notification_email' => 'nullable|email',
        ]);

        $tenant = $user->tenant;
        
        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found',
            ], 404);
        }

        // Update leather consumption mode if provided
        if ($request->has('leather_consumption_mode')) {
            $tenant->leather_consumption_mode = $request->leather_consumption_mode;
        }

        // Get existing settings or initialize
        $settings = $tenant->settings ?? [];
        
        // Merge with new settings
        $settings = array_merge($settings, $request->only([
            'low_stock_threshold_leather',
            'low_stock_threshold_accessories',
            'auto_generate_batch_id',
            'auto_create_invoice',
            'notification_email',
        ]));

        // Remove null values
        $settings = array_filter($settings, function ($value) {
            return $value !== null;
        });

        $tenant->settings = $settings;
        $tenant->save();

        return response()->json([
            'message' => 'Tenant settings updated successfully',
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'leather_consumption_mode' => $tenant->leather_consumption_mode,
            ],
            'settings' => $settings,
        ]);
    }

    /**
     * Export user data
     */
    public function exportUserData(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        try {
            // Collect user data
            $userData = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'department' => $user->department,
                    'position' => $user->position,
                    'employed_on' => $user->employed_on,
                    'address' => $user->address,
                    'country' => $user->country,
                    'emergency_contact' => $user->emergency_contact,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                }),
                'preferences' => $user->preferences ?? [],
            ];

            // Collect tenant data if user is part of a tenant
            if ($tenantId) {
                $tenant = $user->tenant;
                if ($tenant) {
                    $userData['tenant'] = [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                        'leather_consumption_mode' => $tenant->leather_consumption_mode,
                    ];
                }
            }

            // Generate JSON file
            $jsonData = json_encode($userData, JSON_PRETTY_PRINT);
            $filename = 'user_data_' . $user->id . '_' . date('Y-m-d_His') . '.json';

            return response()->json([
                'data' => $userData,
                'filename' => $filename,
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting user data', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to export user data',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }
}

