<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Models\User;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CarImageController;
use App\Http\Controllers\Api\UserAccountController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\AdminStatsController;
use App\Http\Controllers\Api\AdminCarController;
use App\Http\Controllers\Api\AdminCategoryController;
use App\Http\Controllers\Api\AdminBookingController;
use App\Http\Controllers\Api\AdminPaymentController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminReviewController;
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1');

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:5,1');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');
    Route::post('/logout-all-devices', [AuthController::class, 'logoutAllDevices'])
        ->middleware('auth:sanctum');

    Route::get('/profile', [UserProfileController::class, 'show'])
        ->middleware('auth:sanctum');
});

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    if (! $request->hasValidSignature()) {
        abort(403, 'Invalid or expired verification link.');
    }

    $user = User::find($id);

    if (! $user) {
        abort(404, 'User not found.');
    }

    if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        abort(403, 'Invalid verification hash.');
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return view('email-verified');
})->middleware('signed')->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Email is already verified.',
        ], 400);
    }

    $request->user()->sendEmailVerificationNotification();

    return response()->json([
        'message' => 'Verification email sent.',
    ]);
})->middleware(['auth:sanctum', 'throttle:5,1'])->name('verification.send');

Route::apiResource('cars', CarController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('car-images', CarImageController::class);
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{id}', [ReviewController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserProfileController::class, 'show']);
    Route::put('/user/profile', [UserProfileController::class, 'update']);
    Route::post('/avatar', [UserAccountController::class, 'uploadAvatar']);
    Route::post('/user/license', [UserAccountController::class, 'uploadDriverLicense']);
    Route::put('/user/password', [UserAccountController::class, 'updatePassword']);
    Route::delete('/user', [UserAccountController::class, 'destroy']);

    Route::get('/profile', [UserProfileController::class, 'show']);
    Route::put('/profile', [UserProfileController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::patch('/bookings/{id}', [BookingController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::put('/payments/{id}', [PaymentController::class, 'update']);
    Route::patch('/payments/{id}', [PaymentController::class, 'update']);
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);

    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'isAdmin'])->group(function () {
    Route::get('/stats', [AdminStatsController::class, 'index']);
    Route::get('/bookings', [AdminBookingController::class, 'index']);
    Route::get('/bookings/{id}', [AdminBookingController::class, 'show']);
    Route::patch('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus']);
    Route::delete('/bookings/{id}', [AdminBookingController::class, 'destroy']);
    Route::get('/payments', [AdminPaymentController::class, 'index']);
    Route::get('/payments/{id}', [AdminPaymentController::class, 'show']);
    Route::patch('/payments/{id}/status', [AdminPaymentController::class, 'updateStatus']);
    Route::delete('/payments/{id}', [AdminPaymentController::class, 'destroy']);
    Route::get('/cars', [AdminCarController::class, 'index']);
    Route::post('/cars', [AdminCarController::class, 'store']);
    Route::get('/cars/{id}', [AdminCarController::class, 'show']);
    Route::put('/cars/{id}', [AdminCarController::class, 'update']);
    Route::delete('/cars/{id}', [AdminCarController::class, 'destroy']);
    Route::post('/cars/{id}/images', [AdminCarController::class, 'uploadImages']);
    Route::delete('/cars/{id}/images/{imageId}', [AdminCarController::class, 'destroyImage']);
    Route::patch('/cars/{id}/toggle-availability', [AdminCarController::class, 'toggleAvailability']);
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::put('/categories/{id}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy']);
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::patch('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
    Route::get('/reviews', [AdminReviewController::class, 'index']);
    Route::delete('/reviews/{id}', [AdminReviewController::class, 'destroy']);
});

