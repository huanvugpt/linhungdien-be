<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Post::with(['category', 'author', 'approver']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->latest()->paginate(10);
        $categories = Category::where('is_active', true)->get();
        
        return view('admin.posts.index', compact('posts', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.posts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,pending,published',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ]);

        // Auto generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('posts', 'public');
            $validated['featured_image'] = $imagePath;
        }

        // Handle gallery upload
        $gallery = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $path = $file->store('posts/gallery', 'public');
                $gallery[] = $path;
            }
        }
        $validated['gallery'] = $gallery;

        // Set boolean values
        $validated['is_featured'] = $request->has('is_featured');
        $validated['allow_comments'] = $request->has('allow_comments');

        // Set user as author - find or create a user corresponding to the admin
        $admin = Auth::guard('admin')->user();
        $user = \App\Models\User::firstOrCreate(
            ['email' => $admin->email],
            [
                'name' => $admin->name,
                'password' => $admin->password, // Same password hash
                'is_approved' => true,
                'approved_at' => now(),
            ]
        );
        $validated['user_id'] = $user->id;
        
        if ($validated['status'] === 'published') {
            $validated['published_at'] = $validated['published_at'] ?? now();
            $validated['admin_id'] = Auth::guard('admin')->id();
            $validated['approved_at'] = now();
        }

        Post::create($validated);

        return redirect()->route('admin.posts.index')
                        ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $post->load(['category', 'author', 'approver', 'views', 'likes']);
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $post->id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,pending,published,rejected',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ]);

        // Auto generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($post->featured_image && Storage::disk('public')->exists($post->featured_image)) {
                Storage::disk('public')->delete($post->featured_image);
            }
            
            $imagePath = $request->file('featured_image')->store('posts', 'public');
            $validated['featured_image'] = $imagePath;
        }

        // Handle gallery upload
        if ($request->hasFile('gallery')) {
            // Delete old gallery images
            if ($post->gallery) {
                foreach ($post->gallery as $image) {
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }

            $gallery = [];
            foreach ($request->file('gallery') as $file) {
                $path = $file->store('posts/gallery', 'public');
                $gallery[] = $path;
            }
            $validated['gallery'] = $gallery;
        }

        // Set boolean values
        $validated['is_featured'] = $request->has('is_featured');
        $validated['allow_comments'] = $request->has('allow_comments');

        // Handle status change to published
        if ($validated['status'] === 'published' && $post->status !== 'published') {
            $validated['published_at'] = $validated['published_at'] ?? now();
            $validated['admin_id'] = Auth::guard('admin')->id();
            $validated['approved_at'] = now();
        }

        $post->update($validated);

        return redirect()->route('admin.posts.index')
                        ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Delete featured image
        if ($post->featured_image && Storage::disk('public')->exists($post->featured_image)) {
            Storage::disk('public')->delete($post->featured_image);
        }

        // Delete gallery images
        if ($post->gallery) {
            foreach ($post->gallery as $image) {
                if (Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        $post->delete();

        return redirect()->route('admin.posts.index')
                        ->with('success', 'Post deleted successfully.');
    }

    /**
     * Show pending posts for approval
     */
    public function pending()
    {
        $posts = Post::with(['category', 'author'])
                     ->where('status', 'pending')
                     ->latest()
                     ->paginate(10);
        
        return view('admin.posts.pending', compact('posts'));
    }

    /**
     * Approve post
     */
    public function approve(Post $post)
    {
        if ($post->status !== 'pending') {
            return back()->with('error', 'Only pending posts can be approved.');
        }

        $post->update([
            'status' => 'published',
            'published_at' => now(),
            'admin_id' => Auth::guard('admin')->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Post approved and published successfully.');
    }

    /**
     * Reject post
     */
    public function reject(Post $post)
    {
        if ($post->status !== 'pending') {
            return back()->with('error', 'Only pending posts can be rejected.');
        }

        $post->update([
            'status' => 'rejected',
            'admin_id' => Auth::guard('admin')->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Post rejected successfully.');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Post $post)
    {
        $post->update(['is_featured' => !$post->is_featured]);

        $status = $post->is_featured ? 'marked as featured' : 'unmarked as featured';
        
        return back()->with('success', "Post {$status} successfully.");
    }

    /**
     * Bulk approve posts
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'post_ids' => 'required|array',
            'post_ids.*' => 'exists:posts,id'
        ]);

        $posts = Post::whereIn('id', $request->post_ids)
                    ->where('status', 'pending')
                    ->get();

        $count = 0;
        foreach ($posts as $post) {
            $post->update([
                'status' => 'published',
                'approved_by' => Auth::guard('admin')->id(),
                'approved_at' => now(),
                'published_at' => $post->published_at ?? now(),
            ]);
            $count++;
        }

        return back()->with('success', "Successfully approved {$count} posts.");
    }

    /**
     * Bulk reject posts
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'post_ids' => 'required|array',
            'post_ids.*' => 'exists:posts,id'
        ]);

        $posts = Post::whereIn('id', $request->post_ids)
                    ->where('status', 'pending')
                    ->get();

        $count = 0;
        foreach ($posts as $post) {
            $post->update([
                'status' => 'rejected',
                'approved_by' => Auth::guard('admin')->id(),
                'approved_at' => now(),
            ]);
            $count++;
        }

        return back()->with('success', "Successfully rejected {$count} posts.");
    }
}
