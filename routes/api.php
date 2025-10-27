<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\SocialAuthController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\VideoController;
use App\Http\Controllers\API\AlbumController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\QuoteController;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'backend-api'
    ]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API routes
Route::prefix('auth')->group(function () {
    // User Authentication
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');

    // Social Authentication
    Route::get('{provider}', [SocialAuthController::class, 'redirect'])->where('provider', 'google|facebook');
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback'])->where('provider', 'google|facebook');

    // Password Reset - TODO: Create PasswordResetController
    // Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink']);
    // Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);
    // Route::get('reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
});

// Public Posts API routes
Route::prefix('posts')->group(function () {
    // Public post listings
    Route::get('featured', [PostController::class, 'getFeaturedPosts']);
    Route::get('most-viewed', [PostController::class, 'getMostViewedPosts']);
    Route::get('recent', [PostController::class, 'getRecentPosts']);
    Route::get('{slug}', [PostController::class, 'getPost']);
    Route::get('{slug}/related', [PostController::class, 'getRelatedPosts']);
});

// Public Categories API routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'getCategories']);
    Route::get('{slug}', [CategoryController::class, 'getCategory']);
    Route::get('{slug}/posts', [CategoryController::class, 'getCategoryPosts']);
});

// Public Contact API routes
Route::post('contact', [ContactController::class, 'store']);

// Public Videos API routes
Route::prefix('videos')->group(function () {
    Route::get('featured', [VideoController::class, 'getFeaturedVideos']);
    Route::get('most-viewed', [VideoController::class, 'getMostViewedVideos']);
    Route::get('recent', [VideoController::class, 'getRecentVideos']);
    Route::get('search', [VideoController::class, 'searchVideos']);
    Route::get('{id}', [VideoController::class, 'getVideo']);
});

// Public Albums API routes
Route::prefix('albums')->group(function () {
    Route::get('featured', [AlbumController::class, 'getFeaturedAlbums']);
    Route::get('recent', [AlbumController::class, 'getAlbums']);
    Route::get('search', [AlbumController::class, 'searchAlbums']);
    Route::get('{slug}', [AlbumController::class, 'getAlbum']);
});

// Public Quotes API routes
Route::prefix('quotes')->group(function () {
    Route::get('/', [QuoteController::class, 'index']);
    Route::get('random', [QuoteController::class, 'getSingleRandomQuote']);
    Route::get('random-5', [QuoteController::class, 'getRandomQuotes']);
});

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    // User profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('update', [ProfileController::class, 'updateProfile']);
        Route::post('change-password', [ProfileController::class, 'changePassword']);
        Route::delete('delete-account', [ProfileController::class, 'deleteAccount']);
    });

    // Post interactions (requires authentication)
    Route::post('posts/{slug}/like', [PostController::class, 'toggleLike']);
    
    // Video interactions (requires authentication)
    Route::post('videos/{id}/like', [VideoController::class, 'toggleLike']);
    
    // Video management (requires authentication)
    Route::post('videos', [VideoController::class, 'store']);
    Route::put('videos/{video}', [VideoController::class, 'update']);
    Route::delete('videos/{video}', [VideoController::class, 'destroy']);
    
    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{id}', [NotificationController::class, 'destroy']);
    });
    
    // Post submissions
    Route::prefix('submissions')->group(function () {
        Route::get('/', [App\Http\Controllers\PostSubmissionController::class, 'index']);
        Route::post('/', [App\Http\Controllers\PostSubmissionController::class, 'store']);
        Route::get('{postSubmission}', [App\Http\Controllers\PostSubmissionController::class, 'show']);
        Route::put('{postSubmission}', [App\Http\Controllers\PostSubmissionController::class, 'update']);
        Route::delete('{postSubmission}', [App\Http\Controllers\PostSubmissionController::class, 'destroy']);
    });
    
    // Test auth
    Route::get('test-auth', function (Illuminate\Http\Request $request) {
        return response()->json(['user' => $request->user(), 'authenticated' => true]);
    });
    
    // Pusher authentication
    Route::post('broadcasting/auth', function (Illuminate\Http\Request $request) {
        \Log::info('Broadcasting auth request', [
            'user' => $request->user()?->id,
            'socket_id' => $request->input('socket_id'),
            'channel_name' => $request->input('channel_name'),
        ]);
        
        try {
            $result = \Illuminate\Support\Facades\Broadcast::auth($request);
            \Log::info('Broadcasting auth success', ['result' => $result]);
            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Broadcasting auth error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 403);
        }
    });
});

// Get categories for submissions (public)
Route::get('submission-categories', [App\Http\Controllers\PostSubmissionController::class, 'getCategories']);
