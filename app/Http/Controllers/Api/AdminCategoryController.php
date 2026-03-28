<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::withCount('cars')
            ->latest()
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateCategoryPayload($request);
        $category = Category::create($this->prepareCategoryPayload($validated));

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category->loadCount('cars'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $validated = $this->validateCategoryPayload($request, $category->id, true);

        $category->update($this->prepareCategoryPayload($validated, $category));

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category->fresh()->loadCount('cars'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::withCount('cars')->findOrFail($id);

        if ($category->cars_count > 0) {
            return response()->json([
                'message' => 'This category cannot be deleted while cars are assigned to it.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }

    private function validateCategoryPayload(Request $request, ?int $categoryId = null, bool $updating = false): array
    {
        $requiredRule = $updating ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$requiredRule, 'string', 'max:255', Rule::unique('categories', 'name')->ignore($categoryId)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function prepareCategoryPayload(array $validated, ?Category $category = null): array
    {
        $payload = collect($validated)
            ->only(['name', 'description', 'icon'])
            ->toArray();

        if (array_key_exists('slug', $validated) || array_key_exists('name', $validated)) {
            $source = $validated['slug'] ?? $validated['name'] ?? $category?->name ?? Str::random(8);
            $payload['slug'] = $this->generateUniqueSlug($source, $category?->id);
        }

        return $payload;
    }

    private function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = Str::lower(Str::random(8));
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (
            Category::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
