<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateUserRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * @group User Management
 */
class UsersController extends Controller
{
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

    public function store(CreateUserRequest $request)
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $actor->isPrivileged()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();

        if (($validated['role'] ?? null) === 'ADMIN') {
            $validated['role'] = 'Admin';
        }

        if (($validated['role'] ?? null) === 'STAFF') {
            $validated['role'] = 'Staff';
        }

        if ($actor->isAdmin() && in_array($validated['role'], ['Admin', 'SuperAdmin'], true)) {
            return response()->json([
                'message' => 'Admin hanya dapat membuat user dengan role Staff.',
            ], 403);
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

    public function update(UpdateUserRequest $request, User $user)
    {
        $actor = $request->user();

        if (! $actor->isPrivileged()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }

            $validated['foto'] = $request->file('foto')->store('profile-photos', 'public');
        }

        if (($validated['role'] ?? null) === 'ADMIN') {
            $validated['role'] = 'Admin';
        }

        if (($validated['role'] ?? null) === 'STAFF') {
            $validated['role'] = 'Staff';
        }

        if ($actor->isAdmin() && array_key_exists('role', $validated) && in_array($validated['role'], ['Admin', 'SuperAdmin'], true)) {
            return response()->json([
                'message' => 'Admin hanya dapat menetapkan role Staff.',
            ], 403);
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

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus']);
    }

    public function resetPassword(ResetPasswordRequest $request, User $user)
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $actor->isPrivileged()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validated();

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json(['message' => 'Password reset successfully']);
    }

    public function subordinates(Request $request, User $user)
    {
        /** @var User $actor */
        $actor = $request->user();

        if ($actor->isPrivileged()) {
            return UserResource::collection($user->subordinates()->paginate(100));
        }

        if ($actor->id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! $actor->hasSubordinates()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return UserResource::collection($actor->subordinates()->paginate(100));
    }
}
