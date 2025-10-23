<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostSubmission;
use App\Models\Post;
use App\Events\PostSubmissionApproved;
use App\Events\PostSubmissionRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminPostSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = PostSubmission::with(['user', 'category', 'reviewer'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->paginate(15);

        return view('admin.submissions.index', compact('submissions'));
    }

    public function show(PostSubmission $postSubmission)
    {
        $postSubmission->load(['user', 'category', 'reviewer']);
        return view('admin.submissions.show', compact('postSubmission'));
    }

    public function approve(Request $request, PostSubmission $postSubmission)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
            'publish_immediately' => 'boolean'
        ]);

        $postSubmission->update([
            'status' => 'approved',
            'admin_note' => $request->admin_note,
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
        ]);

        // Fire approved event to send notification
        event(new PostSubmissionApproved($postSubmission));

        // If admin chooses to publish immediately, create a Post from the submission
        if ($request->publish_immediately) {
            $this->createPostFromSubmission($postSubmission);
        }

        return redirect()->back()->with('success', 'Bài viết đã được phê duyệt thành công!');
    }

    public function reject(Request $request, PostSubmission $postSubmission)
    {
        $request->validate([
            'admin_note' => 'required|string|max:1000'
        ]);

        $postSubmission->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
        ]);

        // Fire rejected event to send notification
        event(new PostSubmissionRejected($postSubmission));

        return redirect()->back()->with('success', 'Bài viết đã bị từ chối!');
    }

    public function publishApproved(PostSubmission $postSubmission)
    {
        if ($postSubmission->status !== 'approved') {
            return redirect()->back()->with('error', 'Chỉ có thể đăng những bài viết đã được phê duyệt!');
        }

        $this->createPostFromSubmission($postSubmission);

        return redirect()->back()->with('success', 'Bài viết đã được đăng thành công!');
    }

    private function createPostFromSubmission(PostSubmission $submission)
    {
        // Generate slug
        $slug = Str::slug($submission->title);
        $originalSlug = $slug;
        $counter = 1;
        
        // Ensure unique slug
        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Copy images to posts directory
        $featuredImage = null;
        if ($submission->featured_image) {
            $filename = basename($submission->featured_image);
            $newPath = 'posts/featured/' . $filename;
            Storage::disk('public')->copy($submission->featured_image, $newPath);
            $featuredImage = $newPath;
        }

        $images = [];
        if ($submission->images) {
            foreach ($submission->images as $image) {
                $filename = basename($image);
                $newPath = 'posts/images/' . $filename;
                Storage::disk('public')->copy($image, $newPath);
                $images[] = $newPath;
            }
        }

        Post::create([
            'title' => $submission->title,
            'slug' => $slug,
            'content' => $submission->content,
            'excerpt' => $submission->excerpt,
            'category_id' => $submission->category_id,
            'user_id' => $submission->user_id, // Original author
            'admin_id' => auth('admin')->id(), // Approving admin
            'featured_image' => $featuredImage,
            'gallery' => !empty($images) ? $images : null,
            'status' => 'published',
            'published_at' => now(),
            'approved_at' => now(),
        ]);
    }

    public function destroy(PostSubmission $postSubmission)
    {
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

        return redirect()->back()->with('success', 'Bài viết đã được xóa!');
    }
}