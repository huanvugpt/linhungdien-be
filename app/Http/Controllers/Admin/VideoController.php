<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Video::with(['category', 'user']);

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status == 'featured') {
                $query->where('is_featured', true);
            }
        }

        $videos = $query->latest()->paginate(15);
        $categories = Category::all();

        return view('admin.videos.index', compact('videos', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.videos.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'youtube_url' => 'required|url|regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/',
            'category_id' => 'required|exists:categories,id',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Extract YouTube ID
        $youtubeId = $this->extractYouTubeId($request->youtube_url);
        if (!$youtubeId) {
            return back()->withErrors(['youtube_url' => 'URL YouTube không hợp lệ'])->withInput();
        }

        Video::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'description' => $request->description,
            'youtube_url' => $request->youtube_url,
            'youtube_id' => $youtubeId,
            'thumbnail_url' => "https://img.youtube.com/vi/{$youtubeId}/maxresdefault.jpg",
            'category_id' => $request->category_id,
            'user_id' => auth('admin')->id(),
            'is_featured' => $request->boolean('is_featured', false),
            'is_active' => $request->boolean('is_active', true),
            'published_at' => now(),
        ]);

        return redirect()->route('admin.videos.index')->with('success', 'Video đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Video $video)
    {
        $video->load(['category', 'user']);
        return view('admin.videos.show', compact('video'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Video $video)
    {
        $categories = Category::all();
        return view('admin.videos.edit', compact('video', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Video $video)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'youtube_url' => 'required|url|regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/',
            'category_id' => 'required|exists:categories,id',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'is_featured' => $request->boolean('is_featured', false),
            'is_active' => $request->boolean('is_active', true),
        ];

        // Update YouTube info if URL changed
        if ($video->youtube_url !== $request->youtube_url) {
            $youtubeId = $this->extractYouTubeId($request->youtube_url);
            if (!$youtubeId) {
                return back()->withErrors(['youtube_url' => 'URL YouTube không hợp lệ'])->withInput();
            }

            $updateData['youtube_url'] = $request->youtube_url;
            $updateData['youtube_id'] = $youtubeId;
            $updateData['thumbnail_url'] = "https://img.youtube.com/vi/{$youtubeId}/maxresdefault.jpg";
        }

        $video->update($updateData);

        return redirect()->route('admin.videos.index')->with('success', 'Video đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video)
    {
        $video->delete();
        return redirect()->route('admin.videos.index')->with('success', 'Video đã được xóa thành công!');
    }

    /**
     * Show featured videos
     */
    public function featured(Request $request)
    {
        $videos = Video::with(['category', 'user'])
            ->where('is_featured', true)
            ->latest()
            ->paginate(15);

        return view('admin.videos.featured', compact('videos'));
    }

    /**
     * Extract YouTube video ID from URL
     */
    private function extractYouTubeId(string $url): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
