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

        if ($user->isStaff() && ! $user->hasSubordinates()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = User::query();

        if (! $user->isPrivileged()) {
            $query->where('manager_id', $user->id);
        }

        // Search by nama, email, or npp
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('npp', 'LIKE', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSorts = ['nama', 'email', 'npp', 'role', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return UserResource::collection($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $actor->isPrivileged()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'npp' => 'required|string|max:255|unique:users',
            'role' => ['required', Rule::in(['SuperAdmin', 'Admin', 'Staff', 'ADMIN', 'MANAGER', 'STAFF'])],
            'password' => 'required|string|min:8',
            'manager_id' => 'nullable|uuid|exists:users,id',
        ]);

        if (($validated['role'] ?? null) === 'ADMIN') {
            $validated['role'] = 'Admin';
        }

        if (($validated['role'] ?? null) === 'MANAGER') {
            $validated['role'] = 'Manager';
        }

        if (($validated['role'] ?? null) === 'STAFF') {
            $validated['role'] = 'Staff';
        }

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return (new UserResource($user->load('manager')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, User $user)
    {
        $currentUser = $request->user();

        if (! $currentUser->canManageUser($user) && $currentUser->id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return new UserResource($user->load('manager'));
    }

    public function update(Request $request, User $user)
    {
        $actor = $request->user();

        if (! $actor->isPrivileged()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'npp' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users', 'npp')->ignore($user->id)],
            'role' => ['sometimes', 'required', Rule::in(['SuperAdmin', 'Admin', 'Staff', 'ADMIN', 'MANAGER', 'STAFF'])],
            'manager_id' => 'nullable|uuid|exists:users,id',
        ]);

        if (($validated['role'] ?? null) === 'ADMIN') {
            $validated['role'] = 'Admin';
        }

        if (($validated['role'] ?? null) === 'MANAGER') {
            $validated['role'] = 'Manager';
        }

        if (($validated['role'] ?? null) === 'STAFF') {
            $validated['role'] = 'Staff';
        }

        $user->update($validated);

        return new UserResource($user->load('manager'));
    }

    public function destroy(Request $request, User $user)
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $actor->isPrivileged()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus']);
    }

    public function resetPassword(Request $request, User $user)
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $actor->isPrivileged()) {
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
