<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('per_page', 10), 100));

        $query = User::query()->withCount('bookings')->latest();

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));

            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($request->filled('role') && $request->input('role') !== 'all') {
            $query->where('role', $request->string('role')->toString());
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = strtolower($request->string('status')->toString());

            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'suspended') {
                $query->where('is_active', false);
            }
        }

        return response()->json(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with([
            'bookings' => function ($query) {
                $query->latest();
            },
            'bookings.car',
            'bookings.payment',
            'bookings.review',
        ])->findOrFail($id);

        return response()->json($user);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['sometimes', 'string', 'max:20'],
            'role' => ['sometimes', 'string', Rule::in(['admin', 'customer'])],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user->fresh(),
        ]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            throw ValidationException::withMessages([
                'role' => ['Admin users cannot be suspended.'],
            ]);
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return response()->json([
            'message' => $user->is_active
                ? 'User account activated successfully.'
                : 'User account suspended successfully.',
            'user' => $user->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::with(['bookings'])->findOrFail($id);

        if ($user->role === 'admin') {
            throw ValidationException::withMessages([
                'role' => ['Admin users cannot be deleted.'],
            ]);
        }

        $hasActiveBookings = $user->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'ongoing'])
            ->exists();

        if ($hasActiveBookings) {
            throw ValidationException::withMessages([
                'bookings' => ['User cannot be deleted because they still have active bookings.'],
            ]);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}