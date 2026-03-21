<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChangePasswordRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * @group Authentication
 */
class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $loginNpp = (string) $validated['npp'];

        $user = User::query()->where('npp', $loginNpp)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Kredensial tidak valid'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json(['user' => new UserResource($user)]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }

            $validated['foto'] = $request->file('foto')->store('profile-photos', 'public');
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => new UserResource($user->fresh('manager')),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Current password does not match'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->last_password_change = now();
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }
}
