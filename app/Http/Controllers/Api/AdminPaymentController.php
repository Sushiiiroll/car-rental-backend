<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AdminPaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('per_page', 10), 100));

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['all', 'pending', 'paid', 'failed', 'refunded'])],
            'method' => ['nullable', Rule::in(['all', 'cash', 'card', 'gcash', 'maya'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Payment::with(['booking.user', 'booking.car'])
            ->latest();

        if (($validated['status'] ?? 'all') !== 'all') {
            $query->where('status', $validated['status']);
        }

        if (($validated['method'] ?? 'all') !== 'all') {
            $query->where('method', $validated['method']);
        }

        if (! empty($validated['start_date']) || ! empty($validated['end_date'])) {
            $startDate = ! empty($validated['start_date'])
                ? Carbon::parse($validated['start_date'])->startOfDay()
                : null;
            $endDate = ! empty($validated['end_date'])
                ? Carbon::parse($validated['end_date'])->endOfDay()
                : null;

            $query->where(function ($paymentQuery) use ($startDate, $endDate) {
                $paymentQuery->where(function ($datedPaymentQuery) use ($startDate, $endDate) {
                    $datedPaymentQuery->whereNotNull('paid_at');

                    if ($startDate) {
                        $datedPaymentQuery->where('paid_at', '>=', $startDate);
                    }

                    if ($endDate) {
                        $datedPaymentQuery->where('paid_at', '<=', $endDate);
                    }
                })->orWhere(function ($createdPaymentQuery) use ($startDate, $endDate) {
                    $createdPaymentQuery->whereNull('paid_at');

                    if ($startDate) {
                        $createdPaymentQuery->where('created_at', '>=', $startDate);
                    }

                    if ($endDate) {
                        $createdPaymentQuery->where('created_at', '<=', $endDate);
                    }
                });
            });
        }

        if (! empty($validated['search'])) {
            $term = trim($validated['search']);

            $query->where(function ($paymentQuery) use ($term) {
                $paymentQuery
                    ->where('transaction_id', 'like', "%{$term}%")
                    ->orWhere('method', 'like', "%{$term}%")
                    ->orWhereHas('booking', function ($bookingQuery) use ($term) {
                        $bookingQuery
                            ->where('id', $term)
                            ->orWhereHas('user', function ($userQuery) use ($term) {
                                $userQuery
                                    ->where('name', 'like', "%{$term}%")
                                    ->orWhere('email', 'like', "%{$term}%");
                            })
                            ->orWhereHas('car', function ($carQuery) use ($term) {
                                $carQuery
                                    ->where('name', 'like', "%{$term}%")
                                    ->orWhere('brand', 'like', "%{$term}%")
                                    ->orWhere('model', 'like', "%{$term}%");
                            });
                    });
            });
        }

        return response()->json(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    public function show(int $id): JsonResponse
    {
        $payment = Payment::with(['booking.user', 'booking.car'])
            ->findOrFail($id);

        return response()->json($payment);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['paid', 'failed', 'refunded'])],
        ]);

        $payment = Payment::with(['booking.user', 'booking.car'])
            ->findOrFail($id);

        $attributes = [
            'status' => $validated['status'],
        ];

        if ($validated['status'] === 'paid') {
            $attributes['paid_at'] = $payment->paid_at ?? now();
        }

        if ($validated['status'] === 'failed') {
            $attributes['paid_at'] = null;
        }

        $payment->update($attributes);

        return response()->json([
            'message' => 'Payment status updated successfully.',
            'payment' => $payment->fresh(['booking.user', 'booking.car']),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully.',
        ]);
    }
}
