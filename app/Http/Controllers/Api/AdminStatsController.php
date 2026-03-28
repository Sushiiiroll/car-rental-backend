<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class AdminStatsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'total_cars' => Car::count(),
            'total_bookings' => Booking::count(),
            'active_rentals' => Booking::whereIn('status', ['confirmed', 'ongoing'])->count(),
            'total_revenue' => (float) Payment::where('status', 'paid')->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
        ]);
    }
}
