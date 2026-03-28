<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarImage;
use Illuminate\Http\Request;

class CarImageController extends Controller
{
    public function index()
    {
        return CarImage::with('car')->get();
    }

    public function show($id)
    {
        return CarImage::with('car')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $image = CarImage::create($request->all());

        return response()->json($image, 201);
    }

    public function update(Request $request, $id)
    {
        $image = CarImage::findOrFail($id);
        $image->update($request->all());

        return response()->json($image);
    }

    public function destroy($id)
    {
        CarImage::destroy($id);

        return response()->json([
            'message' => 'Image deleted'
        ]);
    }
}