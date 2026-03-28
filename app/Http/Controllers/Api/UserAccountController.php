<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserAccountController extends Controller
{
    public function uploadAvatar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $validated['avatar']->store('avatars', 'public');

        $user->update([
            'avatar' => $path,
        ]);

        return response()->json([
            'message' => 'Avatar uploaded successfully.',
            'user' => $user->fresh(),
            'avatar_url' => asset('storage/' . $path),
        ]);
    }

    public function uploadDriverLicense(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver_license_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user();

        if ($user->driver_license_image) {
            Storage::disk('public')->delete($user->driver_license_image);
        }

        $path = $validated['driver_license_image']->store('driver-licenses', 'public');

        $user->update([
            'driver_license_image' => $path,
        ]);

        return response()->json([
            'message' => 'Driver license image uploaded successfully.',
            'user' => $user->fresh(),
            'driver_license_image_url' => asset('storage/' . $path),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        $user->tokens()->delete();
        $newToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Password updated successfully.',
            'token' => $newToken,
            'user' => $user->fresh(),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->tokens()->delete();

        $payload = [
            'is_active' => false,
        ];

        if (Schema::hasColumn('users', 'deleted_at')) {
            $payload['deleted_at'] = now();
        }

        $user->forceFill($payload)->save();

        return response()->json([
            'message' => 'Account deactivated successfully.',
        ]);
    }
}
