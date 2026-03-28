<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminBookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('per_page', 10), 100));

        $query = Booking::with(['user', 'car', 'payment'])
            ->latest();

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('date')) {
            $date = $request->date('date');
            if ($date) {
                $query->whereDate('start_date', '<=', $date->toDateString())
                    ->whereDate('end_date', '>=', $date->toDateString());
            }
        }

        if ($request->filled('start_date') || $request->filled('end_date')) {
            $startDate = $request->date('start_date');
            $endDate = $request->date('end_date');

            if ($startDate && $endDate) {
                $query->whereDate('start_date', '<=', $endDate->toDateString())
                    ->whereDate('end_date', '>=', $startDate->toDateString());
            } elseif ($startDate) {
                $query->whereDate('end_date', '>=', $startDate->toDateString());
            } elseif ($endDate) {
                $query->whereDate('start_date', '<=', $endDate->toDateString());
            }
        }

        if ($request->filled('user')) {
            $term = trim((string) $request->input('user'));

            $query->whereHas('user', function ($userQuery) use ($term) {
                $userQuery->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        return response()->json(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    public function show(int $id): JsonResponse
    {
        $booking = Booking::with(['user', 'car', 'payment', 'review'])
            ->findOrFail($id);

        return response()->json($booking);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in(['confirm', 'confirmed', 'set ongoing', 'ongoing', 'complete', 'completed', 'cancel', 'cancelled']),
            ],
        ]);

        $booking = Booking::with(['user', 'car', 'payment'])
            ->findOrFail($id);

        $status = match (strtolower($validated['status'])) {
            'confirm' => 'confirmed',
            'confirmed' => 'confirmed',
            'set ongoing' => 'ongoing',
            'ongoing' => 'ongoing',
            'complete' => 'completed',
            'completed' => 'completed',
            'cancel' => 'cancelled',
            'cancelled' => 'cancelled',
            default => $booking->status,
        };

        $booking->update([
            'status' => $status,
        ]);

        return response()->json([
            'message' => 'Booking status updated successfully.',
            'booking' => $booking->fresh(['user', 'car', 'payment', 'review']),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        $status = strtolower((string) $booking->status);

        if (! in_array($status, ['cancelled', 'completed'], true)) {
            throw ValidationException::withMessages([
                'status' => ['Only cancelled or completed bookings can be deleted.'],
            ]);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully.',
        ]);
    }
}
