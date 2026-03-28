<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'available_only' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $availableOnly = filter_var($validated['available_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $perPage = (int) ($validated['per_page'] ?? 12);

        $query = Car::with(['category', 'carImages']);

        if ($startDate && $endDate && $availableOnly) {
            $query->availableForPeriod($startDate, $endDate);
        }

        $cars = $query->paginate($perPage)->appends($request->query());

        if (! $startDate || ! $endDate) {
            return $cars;
        }

        $cars->setCollection(
            $cars->getCollection()->map(function (Car $car) use ($startDate, $endDate) {
                $availableForDates = $car->isAvailableForPeriod($startDate, $endDate);

                $car->setAttribute('requested_start_date', Carbon::parse($startDate)->toDateString());
                $car->setAttribute('requested_end_date', Carbon::parse($endDate)->toDateString());
                $car->setAttribute('is_available_for_dates', $availableForDates);

                return $car;
            })
        );

        return $cars;
    }

    public function show(Request $request, $id)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $car = Car::with([
            'category',
            'carImages',
            'bookings' => fn ($query) => $query
                ->active()
                ->select(['id', 'car_id', 'start_date', 'end_date', 'status'])
                ->orderBy('start_date'),
        ])->findOrFail($id);
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        if (! $startDate || ! $endDate) {
            return $car;
        }

        $car->setAttribute('requested_start_date', Carbon::parse($startDate)->toDateString());
        $car->setAttribute('requested_end_date', Carbon::parse($endDate)->toDateString());
        $car->setAttribute(
            'is_available_for_dates',
            $car->isAvailableForPeriod($startDate, $endDate)
        );

        return $car;
    }

    public function store(Request $request) 
    {
        $car = Car::create($request->all());
        return response()->json($car, 201);
    }

    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);
        $car->update($request->all());
        return response()->json($car);
    }

    public function destroy($id)
    {
        Car::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
