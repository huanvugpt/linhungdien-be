<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories
     */
    public function getCategories(Request $request)
    {
        try {
            $categories = Category::where('is_active', true)
                ->withCount(['posts' => function ($query) {
                    $query->where('status', 'published');
                }])
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description', 'color']);
            
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category by slug
     */
    public function getCategory(Request $request, $slug)
    {
        try {
            $category = Category::where('slug', $slug)
                ->where('is_active', true)
                ->select('id', 'name', 'slug', 'description', 'color')
                ->first();
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category không tồn tại'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $category
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get posts by category with pagination
     */
    public function getCategoryPosts(Request $request, $slug)
    {
        try {
            $category = Category::where('slug', $slug)
                ->where('is_active', true)
                ->first();
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category không tồn tại'
                ], 404);
            }

            $perPage = $request->input('per_page', 12);
            $sortBy = $request->input('sort', 'latest');
            
            $query = Post::with(['category:id,name,slug,color', 'author:id,name,avatar'])
                ->published()
                ->where('category_id', $category->id)
                ->select([
                    'id', 'title', 'slug', 'excerpt', 'featured_image',
                    'published_at', 'views_count', 'likes_count', 'comments_count',
                    'category_id', 'user_id'
                ]);

            // Apply sorting
            switch ($sortBy) {
                case 'oldest':
                    $query->orderBy('published_at', 'asc');
                    break;
                case 'most_viewed':
                    $query->orderBy('views_count', 'desc');
                    break;
                case 'most_liked':
                    $query->orderBy('likes_count', 'desc');
                    break;
                default: // latest
                    $query->orderBy('published_at', 'desc');
                    break;
            }

            $posts = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'posts' => $posts->items(),
                    'pagination' => [
                        'current_page' => $posts->currentPage(),
                        'last_page' => $posts->lastPage(),
                        'per_page' => $posts->perPage(),
                        'total' => $posts->total(),
                        'has_more' => $posts->hasMorePages()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy bài viết',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}