<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            ...$user->toArray(),
            'avatar_url' => $user->avatar ? asset('storage/' . ltrim($user->avatar, '/')) : null,
            'driver_license_image_url' => $user->driver_license_image
                ? asset('storage/' . ltrim($user->driver_license_image, '/'))
                : null,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'driver_license_number' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $user->fill($validated);
        $user->save();
        $freshUser = $user->fresh();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                ...$freshUser->toArray(),
                'avatar_url' => $freshUser->avatar ? asset('storage/' . ltrim($freshUser->avatar, '/')) : null,
                'driver_license_image_url' => $freshUser->driver_license_image
                    ? asset('storage/' . ltrim($freshUser->driver_license_image, '/'))
                    : null,
            ],
        ]);
    }
}
