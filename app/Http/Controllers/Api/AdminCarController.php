<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminCarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'transmission' => ['nullable', Rule::in(['auto', 'manual'])],
            'fuel_type' => ['nullable', Rule::in(['gasoline', 'diesel', 'electric'])],
            'is_available' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Car::with(['category', 'carImages'])
            ->withCount([
                'bookings',
                'bookings as active_bookings_count' => fn ($bookingQuery) => $bookingQuery->active(),
            ])
            ->latest();

        if (! empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($carQuery) use ($search) {
                $carQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('plate_number', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%");
            });
        }

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (! empty($validated['transmission'])) {
            $query->where('transmission', $validated['transmission']);
        }

        if (! empty($validated['fuel_type'])) {
            $query->where('fuel_type', $validated['fuel_type']);
        }

        if (array_key_exists('is_available', $validated)) {
            $query->where('is_available', filter_var($validated['is_available'], FILTER_VALIDATE_BOOLEAN));
        }

        $cars = $query->paginate((int) ($validated['per_page'] ?? 10))->appends($request->query());

        return response()->json($cars);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateCarPayload($request, null, true);

        $car = DB::transaction(function () use ($validated, $request) {
            $car = Car::create($this->extractCarFields($validated, true));

            $this->storeUploadedImages(
                $car,
                $request->file('images', []),
                (int) ($validated['primary_image_index'] ?? 0)
            );

            return $car->fresh(['category', 'carImages']);
        });

        return response()->json([
            'message' => 'Car created successfully.',
            'car' => $car,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $car = Car::with(['category', 'carImages', 'bookings.user', 'bookings.payment'])->findOrFail($id);

        return response()->json($car);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $car = Car::with('carImages')->findOrFail($id);
        $validated = $this->validateCarPayload($request, $car->id, false);

        $car->update($this->extractCarFields($validated));

        return response()->json([
            'message' => 'Car updated successfully.',
            'car' => $car->fresh(['category', 'carImages']),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $car = Car::with(['bookings' => fn ($query) => $query->active(), 'carImages'])->findOrFail($id);

        if ($car->bookings->isNotEmpty()) {
            return response()->json([
                'message' => 'This car cannot be deleted while it has active bookings.',
            ], 422);
        }

        DB::transaction(function () use ($car) {
            foreach ($car->carImages as $image) {
                $this->deleteStoredImage($image->image_path);
            }

            $car->delete();
        });

        return response()->json([
            'message' => 'Car deleted successfully.',
        ]);
    }

    public function uploadImages(Request $request, int $id): JsonResponse
    {
        $car = Car::with('carImages')->findOrFail($id);

        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'primary_image_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $car = DB::transaction(function () use ($car, $request, $validated) {
            $this->storeUploadedImages(
                $car,
                $request->file('images', []),
                isset($validated['primary_image_index']) ? (int) $validated['primary_image_index'] : null
            );

            return $car->fresh(['category', 'carImages']);
        });

        return response()->json([
            'message' => 'Car images uploaded successfully.',
            'car' => $car,
        ], 201);
    }

    public function destroyImage(int $id, int $imageId): JsonResponse
    {
        $car = Car::with('carImages')->findOrFail($id);
        $image = $car->carImages()->findOrFail($imageId);

        DB::transaction(function () use ($car, $image) {
            $wasPrimary = (bool) $image->is_primary;
            $this->deleteStoredImage($image->image_path);
            $image->delete();

            if ($wasPrimary) {
                $replacement = $car->carImages()->oldest()->first();
                if ($replacement) {
                    $replacement->update(['is_primary' => true]);
                }
            }
        });

        return response()->json([
            'message' => 'Car image deleted successfully.',
            'car' => $car->fresh(['category', 'carImages']),
        ]);
    }

    public function toggleAvailability(int $id): JsonResponse
    {
        $car = Car::findOrFail($id);
        $car->update([
            'is_available' => ! $car->is_available,
        ]);

        return response()->json([
            'message' => 'Car availability updated successfully.',
            'car' => $car->fresh(['category', 'carImages']),
        ]);
    }

    private function validateCarPayload(Request $request, ?int $carId = null, bool $requireImages = false): array
    {
        $imageRules = $requireImages ? ['required', 'array', 'min:1'] : ['sometimes', 'array', 'min:1'];

        return $request->validate([
            'name' => [$carId ? 'sometimes' : 'required', 'string', 'max:255'],
            'brand' => [$carId ? 'sometimes' : 'required', 'string', 'max:255'],
            'model' => [$carId ? 'sometimes' : 'required', 'string', 'max:255'],
            'year' => [$carId ? 'sometimes' : 'required', 'integer', 'min:1900', 'max:' . (now()->year + 1)],
            'color' => [$carId ? 'sometimes' : 'required', 'string', 'max:255'],
            'plate_number' => [
                $carId ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('cars', 'plate_number')->ignore($carId),
            ],
            'category_id' => [$carId ? 'sometimes' : 'required', 'integer', 'exists:categories,id'],
            'seats' => [$carId ? 'sometimes' : 'required', 'integer', 'min:1', 'max:100'],
            'transmission' => [$carId ? 'sometimes' : 'required', Rule::in(['auto', 'manual'])],
            'fuel_type' => [$carId ? 'sometimes' : 'required', Rule::in(['gasoline', 'diesel', 'electric'])],
            'price_per_day' => [$carId ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_available' => ['nullable', 'boolean'],
            'images' => $imageRules,
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'primary_image_index' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function extractCarFields(array $validated, bool $creating = false): array
    {
        $fields = collect($validated)
            ->only([
                'name',
                'brand',
                'model',
                'year',
                'color',
                'plate_number',
                'category_id',
                'seats',
                'transmission',
                'fuel_type',
                'price_per_day',
                'mileage',
                'description',
                'is_available',
            ])
            ->toArray();

        if ($creating && ! array_key_exists('mileage', $validated)) {
            $fields['mileage'] = 0;
        }

        return $fields;
    }

    private function storeUploadedImages(Car $car, array $images, ?int $primaryImageIndex = null): void
    {
        if (empty($images)) {
            return;
        }

        $existingImageCount = $car->carImages()->count();
        $createdImageIds = collect();

        foreach ($images as $index => $image) {
            $storedPath = $image->store('cars', 'public');

            $createdImage = CarImage::create([
                'car_id' => $car->id,
                'image_path' => $storedPath,
                'is_primary' => $existingImageCount === 0
                    ? ($primaryImageIndex === null ? $index === 0 : $index === $primaryImageIndex)
                    : $index === $primaryImageIndex,
            ]);

            $createdImageIds->push($createdImage->id);
        }

        if ($primaryImageIndex !== null) {
            $primaryImageId = $createdImageIds->get($primaryImageIndex);
            $primaryImage = $primaryImageId
                ? $car->carImages()->find($primaryImageId)
                : null;

            if ($primaryImage) {
                $car->carImages()->whereKeyNot($primaryImage->id)->update(['is_primary' => false]);
                $primaryImage->update(['is_primary' => true]);
            }
        }
    }

    private function deleteStoredImage(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
