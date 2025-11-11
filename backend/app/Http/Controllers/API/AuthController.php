<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->tenant_id) {
            return response()->json(['message' => 'User must be associated with a tenant'], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user'  => $user->load('roles.permissions', 'tenant'),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles.permissions', 'tenant'),
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ], [
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password.min'       => 'The new password must be at least 8 characters.',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'              => 'sometimes|required|string|max:255',
            'email'             => 'sometimes|required|email|unique:users,email,' . $user->id,
            'address'           => 'nullable|string|max:500',
            'position'          => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
        ], [
            'email.unique' => 'This email is already taken by another user.',
            'email.email'  => 'Please provide a valid email address.',
        ]);

        // Update only the fields that are provided
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('address')) {
            $user->address = $request->address;
        }
        if ($request->has('position')) {
            $user->position = $request->position;
        }
        if ($request->has('emergency_contact')) {
            $user->emergency_contact = $request->emergency_contact;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $user->load('roles.permissions', 'tenant'),
        ]);
    }
}
