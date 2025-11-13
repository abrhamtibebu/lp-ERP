<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProcurementRequest;
use App\Models\ProcurementRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = ProcurementRequest::with([
            'supplier',
            'requestedBy',
            'submittedBy',
            'receivedBy',
            'approvedBy',
            'items'
        ])
        ->orderBy('request_date', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($requests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'request_date' => 'required|date',
            'submitted_by' => 'nullable|exists:users,id',
            'received_by' => 'nullable|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.specifications' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            // Generate request number
            $requestNumber = 'PR-' . strtoupper(Str::random(8)) . '-' . date('Ymd');

            // Create procurement request
            $procurementRequest = ProcurementRequest::create([
                'tenant_id' => auth()->user()->tenant_id,
                'request_number' => $requestNumber,
                'supplier_id' => $request->supplier_id,
                'request_date' => $request->request_date,
                'requested_by' => auth()->id(),
                'submitted_by' => $request->submitted_by,
                'received_by' => $request->received_by,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Create items
            foreach ($request->items as $item) {
                ProcurementRequestItem::create([
                    'procurement_request_id' => $procurementRequest->id,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'specifications' => $item['specifications'] ?? null,
                ]);
            }

            return response()->json(
                $procurementRequest->load(['supplier', 'requestedBy', 'submittedBy', 'receivedBy', 'items']),
                201
            );
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $procurementRequest = ProcurementRequest::where('tenant_id', auth()->user()->tenant_id)
            ->with([
                'supplier',
                'requestedBy',
                'submittedBy',
                'receivedBy',
                'approvedBy',
                'items'
            ])->findOrFail($id);

        return response()->json($procurementRequest);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $procurementRequest = ProcurementRequest::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        // Only allow updates if status is pending
        if ($procurementRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update procurement request that is not pending'
            ], 422);
        }

        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'request_date' => 'sometimes|date',
            'submitted_by' => 'nullable|exists:users,id',
            'received_by' => 'nullable|exists:users,id',
            'items' => 'sometimes|array|min:1',
            'items.*.item_name' => 'required_with:items|string|max:255',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.unit' => 'required_with:items|string|max:50',
            'items.*.specifications' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $procurementRequest) {
            // Update procurement request
            $procurementRequest->update([
                'supplier_id' => $request->supplier_id ?? $procurementRequest->supplier_id,
                'request_date' => $request->request_date ?? $procurementRequest->request_date,
                'submitted_by' => $request->has('submitted_by') ? $request->submitted_by : $procurementRequest->submitted_by,
                'received_by' => $request->has('received_by') ? $request->received_by : $procurementRequest->received_by,
                'notes' => $request->notes ?? $procurementRequest->notes,
            ]);

            // Update items if provided
            if ($request->has('items')) {
                // Delete existing items
                $procurementRequest->items()->delete();

                // Create new items
                foreach ($request->items as $item) {
                    ProcurementRequestItem::create([
                        'procurement_request_id' => $procurementRequest->id,
                        'item_name' => $item['item_name'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'],
                        'specifications' => $item['specifications'] ?? null,
                    ]);
                }
            }

            return response()->json(
                $procurementRequest->load(['supplier', 'requestedBy', 'submittedBy', 'receivedBy', 'items'])
            );
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $procurementRequest = ProcurementRequest::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        // Only allow deletion if status is pending
        if ($procurementRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot delete procurement request that is not pending'
            ], 422);
        }

        $procurementRequest->delete();

        return response()->json(['message' => 'Procurement request deleted successfully']);
    }

    /**
     * Approve a procurement request
     */
    public function approve(Request $request, string $id)
    {
        $procurementRequest = ProcurementRequest::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if ($procurementRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending requests can be approved'
            ], 422);
        }

        $procurementRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_date' => now()->toDateString(),
        ]);

        return response()->json(
            $procurementRequest->load(['supplier', 'requestedBy', 'submittedBy', 'receivedBy', 'approvedBy', 'items'])
        );
    }

    /**
     * Reject a procurement request
     */
    public function reject(Request $request, string $id)
    {
        $procurementRequest = ProcurementRequest::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if ($procurementRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending requests can be rejected'
            ], 422);
        }

        $procurementRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        return response()->json(
            $procurementRequest->load(['supplier', 'requestedBy', 'submittedBy', 'receivedBy', 'approvedBy', 'items'])
        );
    }
}
