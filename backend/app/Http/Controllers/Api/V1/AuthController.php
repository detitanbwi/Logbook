<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
    public function login(Request $request)
    {
        $validated = $request->validate([
            'npp' => 'required|string',
            'password' => 'required|string',
        ]);

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

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'foto' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:5120',
            'alamat' => 'sometimes|nullable|string|max:2000',
            'tempat_lahir' => 'sometimes|nullable|string|max:255',
            'tanggal_lahir' => 'sometimes|nullable|date',
            'nik' => 'sometimes|nullable|string|max:32',
            'npwp' => 'sometimes|nullable|string|max:32',
            'status_kawin' => 'sometimes|nullable|string|max:32',
            'riwayat_pendidikan' => 'sometimes|nullable|array',
            'riwayat_karir' => 'sometimes|nullable|array',
        ]);

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

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

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
