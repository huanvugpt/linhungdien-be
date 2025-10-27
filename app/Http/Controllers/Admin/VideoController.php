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
        $query = Video::with(['category', 'user', 'admin']);

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
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'excerpt' => 'nullable|string|max:500',
                'content' => 'nullable|string',
                'description' => 'nullable|string|max:1000',
                'youtube_url' => 'required|url',
                'category_id' => 'required|exists:categories,id',
                'is_featured' => 'boolean',
                'is_active' => 'boolean',
            ], [
                'title.required' => 'Tiêu đề video là bắt buộc.',
                'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
                'excerpt.max' => 'Tóm tắt không được vượt quá 500 ký tự.',
                'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
                'youtube_url.required' => 'Link YouTube là bắt buộc.',
                'youtube_url.url' => 'Link YouTube không đúng định dạng URL.',
                'category_id.required' => 'Vui lòng chọn danh mục cho video.',
                'category_id.exists' => 'Danh mục được chọn không tồn tại.',
            ]);

            // Extract YouTube ID
            $youtubeId = $this->extractYouTubeId($request->youtube_url);
            if (!$youtubeId) {
                return back()->withErrors(['youtube_url' => 'URL YouTube không hợp lệ. Vui lòng sử dụng định dạng: https://www.youtube.com/watch?v=VIDEO_ID hoặc https://youtu.be/VIDEO_ID'])->withInput();
            }

            $video = Video::create([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'excerpt' => $request->excerpt,
                'content' => $request->content,
                'description' => $request->description,
                'youtube_url' => $request->youtube_url,
                'youtube_id' => $youtubeId,
                'thumbnail_url' => "https://img.youtube.com/vi/{$youtubeId}/maxresdefault.jpg",
                'category_id' => $request->category_id,
                'admin_id' => auth('admin')->id(),
                'user_id' => null, // Created by admin, not by user
                'is_featured' => $request->boolean('is_featured', false),
                'is_active' => $request->boolean('is_active', true),
                'published_at' => now(),
            ]);

            \Log::info('Video created successfully', ['video_id' => $video->id, 'admin_id' => auth('admin')->id()]);

            return redirect()->route('admin.videos.index')->with('success', 'Video đã được tạo thành công!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Video validation failed', ['errors' => $e->errors(), 'admin_id' => auth('admin')->id()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Video database error', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'admin_id' => auth('admin')->id()
            ]);
            return back()->with('error', 'Lỗi cơ sở dữ liệu: Không thể lưu video. Vui lòng kiểm tra lại thông tin và thử lại.')->withInput();
        } catch (\Exception $e) {
            \Log::error('Video creation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'admin_id' => auth('admin')->id(),
                'request_data' => $request->except(['_token'])
            ]);
            
            $errorMessage = 'Có lỗi xảy ra khi tạo video.';
            if (app()->environment('local')) {
                $errorMessage .= ' Lỗi: ' . $e->getMessage();
            }
            
            return back()->with('error', $errorMessage)->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Video $video)
    {
        $video->load(['category', 'user', 'admin']);
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
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'excerpt' => 'nullable|string|max:500',
                'content' => 'nullable|string',
                'description' => 'nullable|string|max:1000',
                'youtube_url' => 'required|url',
                'category_id' => 'required|exists:categories,id',
                'is_featured' => 'boolean',
                'is_active' => 'boolean',
            ], [
                'title.required' => 'Tiêu đề video là bắt buộc.',
                'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
                'excerpt.max' => 'Tóm tắt không được vượt quá 500 ký tự.',
                'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
                'youtube_url.required' => 'Link YouTube là bắt buộc.',
                'youtube_url.url' => 'Link YouTube không đúng định dạng URL.',
                'category_id.required' => 'Vui lòng chọn danh mục cho video.',
                'category_id.exists' => 'Danh mục được chọn không tồn tại.',
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
                    return back()->withErrors(['youtube_url' => 'URL YouTube không hợp lệ. Vui lòng sử dụng định dạng: https://www.youtube.com/watch?v=VIDEO_ID hoặc https://youtu.be/VIDEO_ID'])->withInput();
                }

                $updateData['youtube_url'] = $request->youtube_url;
                $updateData['youtube_id'] = $youtubeId;
                $updateData['thumbnail_url'] = "https://img.youtube.com/vi/{$youtubeId}/maxresdefault.jpg";
            }

            $video->update($updateData);

            \Log::info('Video updated successfully', ['video_id' => $video->id, 'admin_id' => auth('admin')->id()]);

            return redirect()->route('admin.videos.index')->with('success', 'Video đã được cập nhật thành công!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Video update validation failed', ['errors' => $e->errors(), 'admin_id' => auth('admin')->id()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Video update database error', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'admin_id' => auth('admin')->id()
            ]);
            return back()->with('error', 'Lỗi cơ sở dữ liệu: Không thể cập nhật video. Vui lòng kiểm tra lại thông tin và thử lại.')->withInput();
        } catch (\Exception $e) {
            \Log::error('Video update failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'admin_id' => auth('admin')->id(),
                'request_data' => $request->except(['_token'])
            ]);
            
            $errorMessage = 'Có lỗi xảy ra khi cập nhật video.';
            if (app()->environment('local')) {
                $errorMessage .= ' Lỗi: ' . $e->getMessage();
            }
            
            return back()->with('error', $errorMessage)->withInput();
        }
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
        $videos = Video::with(['category', 'user', 'admin'])
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
