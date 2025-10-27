<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\ContactController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Admin routes are separated from web routes for better organization.
| These routes are automatically loaded with:
| - Prefix: 'admin'
| - Name prefix: 'admin.'
| - Middleware: 'web'
|
| Registered in: bootstrap/app.php
|
*/

// Admin Authentication Routes (Guest)
Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [AdminLoginController::class, 'login'])->name('login.post');

// Admin Protected Routes
Route::middleware('admin')->group(function () {
    // Authentication
    Route::post('logout', [AdminLoginController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [DashboardController::class, 'index']);
    
    // User Management
    Route::resource('users', UserController::class);
    Route::get('users/pending/list', [UserController::class, 'pending'])->name('users.pending');
    Route::post('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');
    Route::get('users/search', [UserController::class, 'search'])->name('users.search');
    
    // Category Management
    Route::resource('categories', CategoryController::class);
    Route::post('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
    
    // Post Management
    Route::resource('posts', PostController::class);
    Route::get('posts/pending/list', [PostController::class, 'pending'])->name('posts.pending');
    Route::post('posts/{post}/approve', [PostController::class, 'approve'])->name('posts.approve');
    Route::post('posts/{post}/reject', [PostController::class, 'reject'])->name('posts.reject');
    Route::post('posts/{post}/toggle-featured', [PostController::class, 'toggleFeatured'])->name('posts.toggle-featured');
    Route::post('posts/bulk-approve', [PostController::class, 'bulkApprove'])->name('posts.bulk-approve');
    Route::post('posts/bulk-reject', [PostController::class, 'bulkReject'])->name('posts.bulk-reject');
    
    // Notification Management
    Route::resource('notifications', NotificationController::class);
    Route::get('notifications/statistics', [NotificationController::class, 'stats'])->name('notifications.stats');
    Route::post('notifications/{notification}/send', [NotificationController::class, 'send'])->name('notifications.send');
    Route::post('notifications/{notification}/cancel', [NotificationController::class, 'cancel'])->name('notifications.cancel');
    
    // Album Management
    Route::resource('albums', AlbumController::class);
    Route::post('albums/{album}/upload', [AlbumController::class, 'uploadImages'])->name('albums.upload');
    Route::delete('albums/{album}/images/{image}', [AlbumController::class, 'deleteImage'])->name('albums.images.delete');
    Route::put('albums/{album}/images/{image}', [AlbumController::class, 'updateImage'])->name('albums.images.update');
    Route::post('albums/{album}/cover/{image}', [AlbumController::class, 'setCover'])->name('albums.setCover');
    
    // Post Submission Management
    Route::resource('submissions', App\Http\Controllers\Admin\AdminPostSubmissionController::class, [
        'names' => [
            'index' => 'submissions.index',
            'show' => 'submissions.show',
            'destroy' => 'submissions.destroy',
        ],
        'only' => ['index', 'show', 'destroy']
    ]);
    Route::post('submissions/{postSubmission}/approve', [App\Http\Controllers\Admin\AdminPostSubmissionController::class, 'approve'])->name('submissions.approve');
    Route::post('submissions/{postSubmission}/reject', [App\Http\Controllers\Admin\AdminPostSubmissionController::class, 'reject'])->name('submissions.reject');
    Route::post('submissions/{postSubmission}/publish', [App\Http\Controllers\Admin\AdminPostSubmissionController::class, 'publishApproved'])->name('submissions.publish');
    
    // Video Management
    Route::resource('videos', VideoController::class);
    Route::get('videos/featured/list', [VideoController::class, 'featured'])->name('videos.featured');
    
    // Contact Management
    Route::resource('contacts', ContactController::class)->except(['create', 'edit']);
    Route::patch('contacts/{contact}/status', [ContactController::class, 'updateStatus'])->name('contacts.updateStatus');
    
    // Log Viewer - Admin only
    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->name('logs');
});