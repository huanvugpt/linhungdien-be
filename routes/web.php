<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\User\PostController as UserPostController;

// API info page
Route::get('/', function () {
    return response()->json([
        'name' => 'Linh Ứng Điện Backend API',
        'version' => '1.0.0',
        'status' => 'running',
        'endpoints' => [
            'api_health' => url('/api/health'),
            'api_auth' => url('/api/auth/*'),
            'api_posts' => url('/api/posts'),
            'api_categories' => url('/api/categories'),
            'admin_panel' => url('/admin'),
        ],
        'documentation' => 'See README.md or INSTALL.md for full documentation'
    ]);
})->name('home');

// User authentication is now handled via API only - no web routes needed

// Public Post Routes
Route::get('posts', [PostController::class, 'index'])->name('posts.index');
Route::get('posts/search', [PostController::class, 'search'])->name('posts.search');
Route::get('posts/popular', [PostController::class, 'popular'])->name('posts.popular');
Route::get('posts/latest', [PostController::class, 'latest'])->name('posts.latest');
Route::get('category/{category}', [PostController::class, 'category'])->name('posts.category');
Route::get('post/{post}', [PostController::class, 'show'])->name('posts.show');

// Post interactions are now handled via API only

// Test route for proxy detection (development only)
if (config('app.debug')) {
    Route::get('/proxy-test', function () {
        return response()->json([
            'client_ip' => request()->ip(),
            'client_ips' => request()->ips(),
            'is_secure' => request()->secure(),
            'scheme' => request()->getScheme(),
            'host' => request()->getHost(),
            'headers' => [
                'x-forwarded-for' => request()->header('X-Forwarded-For'),
                'x-forwarded-proto' => request()->header('X-Forwarded-Proto'),
                'x-forwarded-host' => request()->header('X-Forwarded-Host'),
                'x-forwarded-port' => request()->header('X-Forwarded-Port'),
            ],
            'server_vars' => [
                'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
                'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
                'HTTP_X_FORWARDED_PROTO' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null,
            ]
        ]);
    });
}
