<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::where('tenant_id', auth()->user()->tenant_id)
            ->with('createdBy')
            ->get();
        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'cost_center' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'expense_date' => 'required|date',
        ]);

        $expense = Expense::create([
            'tenant_id' => auth()->user()->tenant_id,
            'description' => $request->description,
            'amount' => $request->amount,
            'cost_center' => $request->cost_center,
            'category' => $request->category,
            'expense_date' => $request->expense_date,
            'created_by' => auth()->id(),
        ]);

        return response()->json($expense->load('createdBy'), 201);
    }

    public function show($id)
    {
        $expense = Expense::with('createdBy')->findOrFail($id);
        return response()->json($expense);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $request->validate([
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'cost_center' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:255',
            'expense_date' => 'sometimes|date',
        ]);

        $expense->update($request->only([
            'description', 'amount', 'cost_center', 'category', 'expense_date'
        ]));

        return response()->json($expense->load('createdBy'));
    }
}

