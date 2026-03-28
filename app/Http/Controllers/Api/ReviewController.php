<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'car', 'booking'])->latest();

        if ($request->filled('car_id')) {
            $query->where('car_id', $request->integer('car_id'));
        }

        return response()->json($query->get());
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(
            Review::with(['user', 'car', 'booking'])->findOrFail($id)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = Booking::with(['car', 'review'])
            ->where('id', $validated['booking_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $booking) {
            throw ValidationException::withMessages([
                'booking_id' => ['You can only review your own bookings.'],
            ]);
        }

        if ($booking->status !== 'completed') {
            throw ValidationException::withMessages([
                'booking_id' => ['Only completed bookings can be reviewed.'],
            ]);
        }

        if ($booking->review()->exists()) {
            throw ValidationException::withMessages([
                'booking_id' => ['A review has already been submitted for this booking.'],
            ]);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'car_id' => $booking->car_id,
            'booking_id' => $booking->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($review->load(['user', 'car', 'booking']), 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $review = Review::where('user_id', $request->user()->id)->findOrFail($id);
        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully.',
        ]);
    }
}
