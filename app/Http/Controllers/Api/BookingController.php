<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with(['user', 'car', 'payment'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($bookings);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $booking = Booking::with(['user', 'car', 'payment'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($booking);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'car_id' => ['required', 'integer', 'exists:cars,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'pickup_location' => ['required', 'string', 'max:255'],
            'return_location' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $car = Car::findOrFail($validated['car_id']);

        if (! $car->isAvailableForPeriod($validated['start_date'], $validated['end_date'])) {
            throw ValidationException::withMessages([
                'car_id' => ['The selected car is not available for the requested dates.'],
            ]);
        }

        $startDate = \Illuminate\Support\Carbon::parse($validated['start_date']);
        $endDate = \Illuminate\Support\Carbon::parse($validated['end_date']);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $totalPrice = $totalDays * (float) $car->price_per_day;

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'car_id' => $car->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'pickup_location' => $validated['pickup_location'],
            'return_location' => $validated['return_location'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(
            $booking->load(['user', 'car', 'payment']),
            201
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $booking = Booking::where('user_id', $request->user()->id)->findOrFail($id);
        $booking->update($request->all());

        return response()->json($booking);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $booking = Booking::where('user_id', $request->user()->id)->findOrFail($id);
        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted'
        ]);
    }

            public function cancel(Request $request, int $id): JsonResponse
            {
                $booking = Booking::with(['user', 'car', 'payment'])
                    ->where('user_id', $request->user()->id)
                    ->findOrFail($id);

                $status = strtolower((string) $booking->status);

                if (! in_array($status, ['pending', 'confirmed'], true)) {
                    throw ValidationException::withMessages([
                        'status' => ['Only pending or confirmed bookings can be cancelled.'],
                    ]);
                }

                $booking->update([
                    'status' => 'cancelled',
                ]);

                return response()->json([
                    'message' => 'Booking cancelled successfully.',
                    'booking' => $booking->fresh(['user', 'car', 'payment']),
                ]);
            }
}
