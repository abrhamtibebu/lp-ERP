<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use Illuminate\Http\Request;

class AdvanceController extends Controller
{
    public function index()
    {
        $advances = Advance::with(['user', 'order'])->get();
        return response()->json($advances);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'purpose' => 'nullable|string',
        ]);

        $advance = Advance::create([
            'tenant_id' => auth()->user()->tenant_id,
            'order_id' => $request->order_id,
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'purpose' => $request->purpose,
            'status' => 'pending',
        ]);

        return response()->json($advance->load('user', 'order'), 201);
    }

    public function approve(Request $request, $id)
    {
        $advance = Advance::findOrFail($id);
        $advance->update(['status' => 'approved']);

        return response()->json($advance->load('user', 'order'));
    }

    public function reject(Request $request, $id)
    {
        $advance = Advance::findOrFail($id);
        $advance->update(['status' => 'rejected']);

        return response()->json($advance->load('user', 'order'));
    }
}

