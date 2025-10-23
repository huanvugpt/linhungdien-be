<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of published posts.
     */
    public function index(Request $request)
    {
        $query = Post::with(['category', 'author'])
                     ->published()
                     ->orderBy('is_featured', 'desc')
                     ->orderBy('published_at', 'desc');

        // Filter by category
        if ($request->has('category')) {
            $category = Category::where('slug', $request->get('category'))->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(12);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $featuredPosts = Post::published()->featured()->take(5)->get();
        
        return view('posts.index', compact('posts', 'categories', 'featuredPosts'));
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        // Check if post is published
        if (!$post->isPublished()) {
            abort(404);
        }

        $post->load(['category', 'author', 'approver']);

        // Track view
        $userId = Auth::id();
        $ipAddress = request()->ip();
        $userAgent = request()->header('User-Agent');
        
        $post->incrementViews($userId, $ipAddress, $userAgent);

        // Get related posts
        $relatedPosts = Post::published()
                           ->where('category_id', $post->category_id)
                           ->where('id', '!=', $post->id)
                           ->take(4)
                           ->get();

        return view('posts.show', compact('post', 'relatedPosts'));
    }

    /**
     * Display posts by category.
     */
    public function category(Category $category)
    {
        $posts = Post::with(['author'])
                     ->published()
                     ->where('category_id', $category->id)
                     ->orderBy('is_featured', 'desc')
                     ->orderBy('published_at', 'desc')
                     ->paginate(12);

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('posts.category', compact('posts', 'category', 'categories'));
    }

    /**
     * Like/Unlike a post (AJAX)
     */
    public function toggleLike($slug, Request $request)
    {
        $user = $request->user() ?? Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to like posts.',
            ], 401);
        }

        $post = Post::where('slug', $slug)->published()->first();
        
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.'
            ], 404);
        }

        $liked = $post->toggleLike($user->id);

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $post->likes_count,
            'message' => $liked ? 'Post liked!' : 'Post unliked!'
        ]);
    }

    /**
     * Search posts
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return redirect()->route('posts.index');
        }

        $posts = Post::with(['category', 'author'])
                     ->published()
                     ->where(function($q) use ($query) {
                         $q->where('title', 'like', "%{$query}%")
                           ->orWhere('content', 'like', "%{$query}%")
                           ->orWhere('excerpt', 'like', "%{$query}%")
                           ->orWhere('meta_keywords', 'like', "%{$query}%");
                     })
                     ->orderBy('published_at', 'desc')
                     ->paginate(12);

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('posts.search', compact('posts', 'query', 'categories'));
    }

    /**
     * Get popular posts (most viewed)
     */
    public function popular()
    {
        $posts = Post::with(['category', 'author'])
                     ->published()
                     ->orderBy('views_count', 'desc')
                     ->paginate(12);

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('posts.popular', compact('posts', 'categories'));
    }

    /**
     * Get latest posts
     */
    public function latest()
    {
        $posts = Post::with(['category', 'author'])
                     ->published()
                     ->orderBy('published_at', 'desc')
                     ->paginate(12);

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('posts.latest', compact('posts', 'categories'));
    }

    // API Methods

    /**
     * Get a specific post by slug for API
     */
    public function getPost($slug, Request $request)
    {
        $user = $request->user();
        
        $post = Post::with(['category', 'author'])
                    ->published()
                    ->where('slug', $slug)
                    ->first();

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Increment view count
        $post->increment('views_count');

        // Check if user liked this post
        if ($user) {
            $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
        }

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    /**
     * Get related posts for API
     */
    public function getRelatedPosts($slug, Request $request)
    {
        $post = Post::where('slug', $slug)->first();
        
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $relatedPosts = Post::with(['category', 'author'])
                           ->published()
                           ->where('id', '!=', $post->id)
                           ->where('category_id', $post->category_id)
                           ->orderBy('published_at', 'desc')
                           ->limit(4)
                           ->get();

        return response()->json([
            'success' => true,
            'data' => $relatedPosts
        ]);
    }

    /**
     * Get featured posts for API
     */
    public function getFeaturedPosts()
    {
        $posts = Post::with(['category', 'author'])
                     ->published()
                     ->featured()
                     ->orderBy('published_at', 'desc')
                     ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }

    /**
     * Get most viewed posts for API
     */
    public function getMostViewedPosts()
    {
        $posts = Post::with(['category', 'author'])
                     ->published()
                     ->orderBy('views_count', 'desc')
                     ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }

    /**
     * Get recent posts for API
     */
    public function getRecentPosts()
    {
        $posts = Post::with(['category', 'author'])
                     ->published()
                     ->orderBy('published_at', 'desc')
                     ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ]);
    }


}
