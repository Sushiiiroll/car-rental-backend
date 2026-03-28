<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::with(['user', 'booking.car'])
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json($reviews);
    }

    public function destroy(int $id)
    {
        $review = Review::findOrFail($id);

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully.',
        ]);
    }
}