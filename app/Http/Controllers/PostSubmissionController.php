<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PostSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostSubmissionController extends Controller
{
    public function index()
    {
        $submissions = auth()->user()->postSubmissions()
            ->with(['category', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($submissions);
    }

    public function show(PostSubmission $postSubmission)
    {
        // Ensure user can only view their own submissions
        if ($postSubmission->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $postSubmission->load(['category', 'reviewer']);
        return response()->json($postSubmission);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('submissions/featured', 'public');
        }

        // Handle additional images
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('submissions/images', 'public');
            }
            $validated['images'] = $imagePaths;
        }

        $validated['user_id'] = auth()->id();
        $validated['submitted_at'] = now();

        $submission = PostSubmission::create($validated);

        return response()->json([
            'message' => 'Bài viết đã được gửi và đang chờ duyệt',
            'submission' => $submission->load('category')
        ], 201);
    }

    public function update(Request $request, PostSubmission $postSubmission)
    {
        // Ensure user can only edit their own submissions and only pending ones
        if ($postSubmission->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($postSubmission->status !== 'pending') {
            return response()->json(['message' => 'Chỉ có thể chỉnh sửa bài viết đang chờ duyệt'], 400);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($postSubmission->featured_image) {
                Storage::disk('public')->delete($postSubmission->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('submissions/featured', 'public');
        }

        // Handle additional images
        if ($request->hasFile('images')) {
            // Delete old images if exist
            if ($postSubmission->images) {
                foreach ($postSubmission->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('submissions/images', 'public');
            }
            $validated['images'] = $imagePaths;
        }

        $postSubmission->update($validated);

        return response()->json([
            'message' => 'Bài viết đã được cập nhật',
            'submission' => $postSubmission->load('category')
        ]);
    }

    public function destroy(PostSubmission $postSubmission)
    {
        // Ensure user can only delete their own submissions and only pending ones
        if ($postSubmission->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($postSubmission->status !== 'pending') {
            return response()->json(['message' => 'Chỉ có thể xóa bài viết đang chờ duyệt'], 400);
        }

        // Delete associated files
        if ($postSubmission->featured_image) {
            Storage::disk('public')->delete($postSubmission->featured_image);
        }
        
        if ($postSubmission->images) {
            foreach ($postSubmission->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $postSubmission->delete();

        return response()->json(['message' => 'Bài viết đã được xóa']);
    }

    public function getCategories()
    {
        $categories = Category::select('id', 'name', 'slug', 'description')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }
}
