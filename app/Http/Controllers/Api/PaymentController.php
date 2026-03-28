<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\PaymentReceiptNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::with(['booking.car', 'booking.user'])
            ->whereHas('booking', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->latest()
            ->get();

        return response()->json($payments);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $payment = Payment::with(['booking.car', 'booking.user'])
            ->whereHas('booking', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->findOrFail($id);

        return response()->json($payment);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'method' => ['required', 'in:cash,card,gcash,maya'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
        ]);

        $booking = Booking::where('user_id', $request->user()->id)
            ->with(['payment', 'car', 'user'])
            ->findOrFail($validated['booking_id']);

        if ($booking->payment) {
            throw ValidationException::withMessages([
                'booking_id' => ['A payment record already exists for this booking.'],
            ]);
        }

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_price,
            'method' => $validated['method'],
            'status' => 'pending',
            'transaction_id' => $validated['transaction_id'] ?? null,
            'paid_at' => null,
        ]);

        $payment->load(['booking.car', 'booking.user']);

        if ($booking->user) {
            $user = $booking->user;
            $paymentId = $payment->id;

            dispatch(function () use ($user, $paymentId) {
                $freshPayment = Payment::with(['booking.car', 'booking.user'])->find($paymentId);

                if (! $freshPayment) {
                    return;
                }

                $user->notify(new PaymentReceiptNotification($freshPayment));
            })->afterResponse();
        }

        return response()->json(
            $payment,
            201
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'method' => ['sometimes', 'in:cash,card,gcash,maya'],
            'status' => ['sometimes', 'in:pending,paid,failed,refunded'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $payment = Payment::whereHas('booking', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($id);

        $payment->update($validated);

        return response()->json($payment->fresh(['booking.car', 'booking.user']));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $payment = Payment::whereHas('booking', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($id);

        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted'
        ]);
    }
}
