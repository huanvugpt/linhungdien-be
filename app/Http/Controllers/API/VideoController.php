<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Http\Requests\VideoRequest;
use App\Http\Resources\VideoResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class VideoController extends Controller
{
    /**
     * Get featured videos for homepage
     */
    public function getFeaturedVideos(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 6);
            $page = $request->get('page', 1);

            $videos = Video::active()
                ->featured()
                ->with(['category', 'user'])
                ->recent()
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách video nổi bật thành công',
                'data' => [
                    'videos' => $videos->items(),
                    'pagination' => [
                        'current_page' => $videos->currentPage(),
                        'last_page' => $videos->lastPage(),
                        'per_page' => $videos->perPage(),
                        'total' => $videos->total(),
                        'has_more' => $videos->hasMorePages(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách video nổi bật',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get most viewed videos
     */
    public function getMostViewedVideos(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 12);
            $page = $request->get('page', 1);

            $videos = Video::active()
                ->with(['category', 'user'])
                ->mostViewed()
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách video xem nhiều thành công',
                'data' => [
                    'videos' => $videos->items(),
                    'pagination' => [
                        'current_page' => $videos->currentPage(),
                        'last_page' => $videos->lastPage(),
                        'per_page' => $videos->perPage(),
                        'total' => $videos->total(),
                        'has_more' => $videos->hasMorePages(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách video xem nhiều',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent videos with filtering
     */
    public function getRecentVideos(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 12);
            $page = $request->get('page', 1);
            $categoryId = $request->get('category_id');

            $query = Video::active()->with(['category', 'user']);

            // Filter by category if provided
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            $videos = $query->recent()->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách video thành công',
                'data' => [
                    'videos' => $videos->items(),
                    'pagination' => [
                        'current_page' => $videos->currentPage(),
                        'last_page' => $videos->lastPage(),
                        'per_page' => $videos->perPage(),
                        'total' => $videos->total(),
                        'has_more' => $videos->hasMorePages(),
                    ],
                    'category_id' => $categoryId
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single video by ID
     */
    public function getVideo(Request $request, $identifier): JsonResponse
    {
        try {
            $video = Video::active()
                ->with(['category', 'user'])
                ->where('id', $identifier)
                ->first();

            if (!$video) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video không tồn tại'
                ], 404);
            }

            // Increment views count
            $video->incrementViews();

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin video thành công',
                'data' => [
                    'video' => $video
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle like for video
     */
    public function toggleLike(Request $request, $id): JsonResponse
    {
        try {
            $video = Video::active()->find($id);

            if (!$video) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video không tồn tại'
                ], 404);
            }

            // For now, just increment likes (later can implement user-specific likes)
            $video->increment('likes_count');

            return response()->json([
                'success' => true,
                'message' => 'Đã thích video',
                'data' => [
                    'is_liked' => true,
                    'likes_count' => $video->fresh()->likes_count
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thích video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search videos
     */
    public function searchVideos(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2',
                'per_page' => 'integer|min:1|max:50',
                'page' => 'integer|min:1'
            ]);

            $query = $request->get('q');
            $perPage = $request->get('per_page', 12);
            $page = $request->get('page', 1);

            $videos = Video::active()
                ->with(['category', 'user'])
                ->where(function($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->recent()
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Tìm kiếm video thành công',
                'data' => [
                    'videos' => $videos->items(),
                    'pagination' => [
                        'current_page' => $videos->currentPage(),
                        'last_page' => $videos->lastPage(),
                        'per_page' => $videos->perPage(),
                        'total' => $videos->total(),
                        'has_more' => $videos->hasMorePages(),
                    ],
                    'query' => $query
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tìm kiếm video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new video
     */
    public function store(VideoRequest $request): JsonResponse
    {
        try {

            // Extract YouTube ID from URL
            $youtubeId = $this->extractYouTubeId($request->youtube_url);
            if (!$youtubeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL YouTube không hợp lệ'
                ], 422);
            }

            // Get YouTube video info
            $videoInfo = $this->getYouTubeVideoInfo($youtubeId);

            $video = Video::create([
                'title' => $request->title,
                'slug' => \Str::slug($request->title),
                'excerpt' => $request->excerpt,
                'content' => $request->content,
                'description' => $request->description,
                'youtube_url' => $request->youtube_url,
                'youtube_id' => $youtubeId,
                'thumbnail_url' => $videoInfo['thumbnail'] ?? "https://img.youtube.com/vi/{$youtubeId}/maxresdefault.jpg",
                'duration' => $videoInfo['duration'] ?? null,
                'category_id' => $request->category_id,
                'user_id' => auth()->id(),
                'is_featured' => $request->boolean('is_featured', false),
                'is_active' => $request->boolean('is_active', true),
                'published_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tạo video thành công',
                'data' => new VideoResource($video->load(['category', 'user']))
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a video
     */
    public function update(VideoRequest $request, Video $video): JsonResponse
    {
        try {
            // Check ownership
            if ($video->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền sửa video này'
                ], 403);
            }



            $updateData = $request->only([
                'title', 'excerpt', 'content', 'description', 
                'category_id', 'is_featured', 'is_active'
            ]);

            // Update slug if title changed
            if ($request->has('title')) {
                $updateData['slug'] = \Str::slug($request->title);
            }

            // Update YouTube info if URL changed
            if ($request->has('youtube_url')) {
                $youtubeId = $this->extractYouTubeId($request->youtube_url);
                if (!$youtubeId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'URL YouTube không hợp lệ'
                    ], 422);
                }

                $videoInfo = $this->getYouTubeVideoInfo($youtubeId);
                $updateData['youtube_url'] = $request->youtube_url;
                $updateData['youtube_id'] = $youtubeId;
                $updateData['thumbnail_url'] = $videoInfo['thumbnail'] ?? "https://img.youtube.com/vi/{$youtubeId}/maxresdefault.jpg";
                $updateData['duration'] = $videoInfo['duration'] ?? null;
            }

            $video->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật video thành công',
                'data' => new VideoResource($video->fresh()->load(['category', 'user']))
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a video
     */
    public function destroy(Video $video): JsonResponse
    {
        try {
            // Check ownership
            if ($video->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xóa video này'
                ], 403);
            }

            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa video thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa video',
                'error' => $e->getMessage()
            ], 500);
        }
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

    /**
     * Get YouTube video information
     */
    private function getYouTubeVideoInfo(string $youtubeId): array
    {
        try {
            // You can integrate with YouTube API here if needed
            // For now, just return basic info
            return [
                'thumbnail' => "https://img.youtube.com/vi/{$youtubeId}/maxresdefault.jpg",
                'duration' => null, // Can be fetched from YouTube API
            ];
        } catch (\Exception $e) {
            return [
                'thumbnail' => "https://img.youtube.com/vi/{$youtubeId}/maxresdefault.jpg",
                'duration' => null,
            ];
        }
    }
}