<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AlbumController extends Controller
{
    /**
     * Get featured albums for homepage
     */
    public function getFeaturedAlbums(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 4);
            $page = $request->get('page', 1);

            $albums = Album::active()
                ->with(['images' => function($query) {
                    $query->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách album thành công',
                'data' => [
                    'albums' => $albums->items(),
                    'pagination' => [
                        'current_page' => $albums->currentPage(),
                        'last_page' => $albums->lastPage(),
                        'per_page' => $albums->perPage(),
                        'total' => $albums->total(),
                        'has_more' => $albums->hasMorePages(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách album',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all albums with filtering
     */
    public function getAlbums(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 12);
            $page = $request->get('page', 1);

            $albums = Album::active()
                ->with(['images' => function($query) {
                    $query->orderBy('sort_order')->limit(1);
                }])
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách album thành công',
                'data' => [
                    'albums' => $albums->items(),
                    'pagination' => [
                        'current_page' => $albums->currentPage(),
                        'last_page' => $albums->lastPage(),
                        'per_page' => $albums->perPage(),
                        'total' => $albums->total(),
                        'has_more' => $albums->hasMorePages(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách album',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single album with all images
     */
    public function getAlbum(Request $request, $slug): JsonResponse
    {
        try {
            $album = Album::active()
                ->with(['images' => function($query) {
                    $query->orderBy('sort_order');
                }])
                ->where('slug', $slug)
                ->first();

            if (!$album) {
                return response()->json([
                    'success' => false,
                    'message' => 'Album không tồn tại'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin album thành công',
                'data' => [
                    'album' => $album
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin album',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search albums
     */
    public function searchAlbums(Request $request): JsonResponse
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

            $albums = Album::active()
                ->with(['images' => function($query) {
                    $query->orderBy('sort_order')->limit(1);
                }])
                ->where(function($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Tìm kiếm album thành công',
                'data' => [
                    'albums' => $albums->items(),
                    'pagination' => [
                        'current_page' => $albums->currentPage(),
                        'last_page' => $albums->lastPage(),
                        'per_page' => $albums->perPage(),
                        'total' => $albums->total(),
                        'has_more' => $albums->hasMorePages(),
                    ],
                    'query' => $query
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tìm kiếm album',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}