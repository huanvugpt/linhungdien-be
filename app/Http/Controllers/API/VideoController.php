<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Video;
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
}