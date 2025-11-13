<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = User::where('tenant_id', auth()->user()->tenant_id)
            ->with('roles', 'employeeDocuments')
            ->get();

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'country' => 'nullable|string|size:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'department' => 'required|string|in:HR,Inventory,Production,Logistics,Finance,Operations',
            'position' => 'required|string|max:255',
            'employed_on' => 'required|date',
            'emergency_contact' => 'required|string|max:500',
            'documents' => 'array',
            'documents.*' => 'file|max:10240', // 10MB max
        ]);

        $employee = User::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'address' => $request->address,
            'country' => $request->country,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'department' => $request->department,
            'position' => $request->position,
            'employed_on' => $request->employed_on,
            'emergency_contact' => $request->emergency_contact,
        ]);

        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('employee_documents', 'public');
                
                EmployeeDocument::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'user_id' => $employee->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'document_type' => $file->getClientMimeType(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return response()->json($employee->load('employeeDocuments'), 201);
    }

    public function show($id)
    {
        $employee = User::where('tenant_id', auth()->user()->tenant_id)
            ->with('roles', 'employeeDocuments')
            ->findOrFail($id);

        return response()->json($employee);
    }

    public function update(Request $request, $id)
    {
        $employee = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'nullable|string|max:500',
            'country' => 'nullable|string|size:2',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'department' => 'sometimes|string|in:HR,Inventory,Production,Logistics,Finance,Operations',
            'position' => 'sometimes|string|max:255',
            'employed_on' => 'sometimes|date',
            'emergency_contact' => 'sometimes|string|max:500',
            'documents' => 'array',
            'documents.*' => 'file|max:10240',
        ]);

        $employee->update($request->only([
            'name', 'address', 'country', 'email', 'department', 'position', 
            'employed_on', 'emergency_contact'
        ]));

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('employee_documents', 'public');
                
                EmployeeDocument::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'user_id' => $employee->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'document_type' => $file->getClientMimeType(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return response()->json($employee->load('employeeDocuments'));
    }

    public function destroy($id)
    {
        $employee = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        
        // Prevent deleting yourself
        if ($employee->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 422);
        }

        // Delete associated documents
        $documents = EmployeeDocument::where('user_id', $employee->id)->get();
        foreach ($documents as $document) {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }
}

