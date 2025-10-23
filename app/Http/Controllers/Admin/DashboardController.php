<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\PostLike;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        $stats = [
            // User Stats
            'total_users' => User::count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'approved_users' => User::where('status', 'approved')->count(),
            'rejected_users' => User::where('status', 'rejected')->count(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
            
            // Post Stats
            'total_posts' => Post::count(),
            'published_posts' => Post::where('status', 'published')->count(),
            'pending_posts' => Post::where('status', 'pending')->count(),
            'draft_posts' => Post::where('status', 'draft')->count(),
            'total_views' => Post::sum('views_count'),
            'total_likes' => Post::sum('likes_count'),
            
            // Category Stats
            'total_categories' => Category::count(),
            'active_categories' => Category::where('is_active', true)->count(),
        ];
        
        $recent_users = User::with('approvedBy')->latest()->take(5)->get();
        $recent_posts = Post::with(['category', 'author'])->latest()->take(5)->get();
        $popular_posts = Post::published()->orderBy('views_count', 'desc')->take(5)->get();
        
        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_posts', 'popular_posts'));
    }
}
