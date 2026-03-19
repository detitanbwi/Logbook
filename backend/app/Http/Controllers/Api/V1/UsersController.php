<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * @group User Management
 */
class UsersController extends Controller
{
    public function __construct()
    {
        // Require specific roles depending on action if not done in routes
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = User::query();

        if ($user->role === 'MANAGER') {
            $query->where('manager_id', $user->id);
        } elseif ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Search by name, email, or nip
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('nip', 'LIKE', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSorts = ['name', 'email', 'nip', 'role', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return UserResource::collection($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nip' => 'required|string|max:255|unique:users',
            'role' => ['required', Rule::in(['ADMIN', 'MANAGER', 'STAFF'])],
            'password' => 'required|string|min:8',
            'manager_id' => 'nullable|uuid|exists:users,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function show(Request $request, User $user)
    {
        $currentUser = $request->user();

        if ($currentUser->role !== 'ADMIN' && ($currentUser->role !== 'MANAGER' || $user->manager_id !== $currentUser->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        if ($request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'nip' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['sometimes', 'required', Rule::in(['ADMIN', 'MANAGER', 'STAFF'])],
            'manager_id' => 'nullable|uuid|exists:users,id',
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus']);
    }

    public function resetPassword(Request $request, User $user)
    {
        if ($request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'new_password' => 'required|string|min:8',
        ]);

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
