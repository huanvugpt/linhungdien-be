<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    /**
     * Get featured posts
     */
    public function getFeaturedPosts(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 6);
            $page = $request->input('page', 1);
            
            $posts = Post::with(['category:id,name,slug,color', 'author:id,name,avatar'])
                ->published()
                ->featured()
                ->recent()
                ->select([
                    'id', 'title', 'slug', 'excerpt', 'featured_image', 
                    'published_at', 'views_count', 'likes_count', 'comments_count',
                    'category_id', 'user_id'
                ])
                ->paginate($perPage, ['*'], 'page', $page);

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3333');
            $formattedPosts = collect($posts->items())->map(function($post) use ($frontendUrl) {
                $postData = $post->toArray();
                $postData['featured_image_url'] = $frontendUrl . '/storage/' . $post->featured_image;
                if ($post->gallery) {
                    $postData['gallery_urls'] = array_map(function($image) use ($frontendUrl) {
                        return $frontendUrl . '/storage/' . $image;
                    }, $post->gallery);
                }
                return $postData;
            })->all();

            return response()->json([
                'success' => true,
                'message' => 'Featured posts retrieved successfully',
                'data' => [
                    'posts' => $formattedPosts,
                    'pagination' => [
                        'current_page' => $posts->currentPage(),
                        'last_page' => $posts->lastPage(),
                        'per_page' => $posts->perPage(),
                        'total' => $posts->total(),
                        'has_more' => $posts->hasMorePages()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get most viewed posts
     */
    public function getMostViewedPosts(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $timeframe = $request->input('timeframe', 'all'); // all, week, month
            
            $query = Post::with(['category:id,name,slug,color', 'author:id,name,avatar'])
                ->published()
                ->select([
                    'id', 'title', 'slug', 'excerpt', 'featured_image', 
                    'published_at', 'views_count', 'likes_count', 'comments_count',
                    'category_id', 'user_id'
                ]);

            // Filter by timeframe if specified
            switch ($timeframe) {
                case 'week':
                    $query->where('published_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('published_at', '>=', now()->subMonth());
                    break;
                case 'all':
                default:
                    // No additional filter
                    break;
            }

            $posts = $query->mostViewed()
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Most viewed posts retrieved successfully',
                'data' => [
                    'posts' => $posts->items(),
                    'timeframe' => $timeframe,
                    'pagination' => [
                        'current_page' => $posts->currentPage(),
                        'last_page' => $posts->lastPage(),
                        'per_page' => $posts->perPage(),
                        'total' => $posts->total(),
                        'has_more' => $posts->hasMorePages()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve most viewed posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent posts
     */
    public function getRecentPosts(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $categoryId = $request->input('category_id');
            
            $query = Post::with(['category:id,name,slug,color', 'author:id,name,avatar'])
                ->published()
                ->select([
                    'id', 'title', 'slug', 'excerpt', 'featured_image', 
                    'published_at', 'views_count', 'likes_count', 'comments_count',
                    'category_id', 'user_id'
                ]);

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            $posts = $query->recent()
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Recent posts retrieved successfully',
                'data' => [
                    'posts' => $posts->items(),
                    'category_id' => $categoryId,
                    'pagination' => [
                        'current_page' => $posts->currentPage(),
                        'last_page' => $posts->lastPage(),
                        'per_page' => $posts->perPage(),
                        'total' => $posts->total(),
                        'has_more' => $posts->hasMorePages()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recent posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single post details
     */
    public function getPost(Request $request, string $slug): JsonResponse
    {
        try {
            $post = Post::with([
                'category:id,name,slug,color',
                'author:id,name,avatar'
            ])
            ->published()
            ->where('slug', $slug)
            ->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found or not published'
                ], 404);
            }

            // Increment views if needed
            $userId = auth('sanctum')->id();
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
            
            $post->incrementViews($userId, $ipAddress, $userAgent);

            // Format post data with image URLs
            $postData = $post->toArray();
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3333');
            $postData['featured_image_url'] = $frontendUrl . '/storage/' . $post->featured_image;
            
            if ($post->gallery) {
                $postData['gallery_urls'] = array_map(function($image) use ($frontendUrl) {
                    return $frontendUrl . '/storage/' . $image;
                }, $post->gallery);
            }

            return response()->json([
                'success' => true,
                'message' => 'Post retrieved successfully',
                'data' => [
                    'post' => $postData,
                    'is_liked' => $userId ? $post->isLikedBy($userId) : false
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle post like
     */
    public function toggleLike(Request $request, string $slug): JsonResponse
    {
        try {
            $user = auth('sanctum')->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $post = Post::published()->where('slug', $slug)->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            $isLiked = $post->toggleLike($user->id);

            return response()->json([
                'success' => true,
                'message' => $isLiked ? 'Post liked successfully' : 'Post unliked successfully',
                'data' => [
                    'is_liked' => $isLiked,
                    'likes_count' => $post->fresh()->likes_count
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle like',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get related posts for a specific post
     */
    // public function getRelatedPosts(Request $request, string $slug): JsonResponse
    // {
    //     try {
    //         $post = Post::published()->where('slug', $slug)->first();
            
    //         if (!$post) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Post not found'
    //             ], 404);
    //         }

    //         $limit = $request->input('limit', 5);
            
    //         // Get related posts from same category, excluding current post
    //         $relatedPosts = Post::with(['category:id,name,slug'])
    //             ->published()
    //             ->where('category_id', $post->category_id)
    //             ->where('id', '!=', $post->id)
    //             ->select([
    //                 'id', 'title', 'slug', 'excerpt', 'featured_image',
    //                 'published_at', 'views_count', 'category_id'
    //             ])
    //             ->orderBy('published_at', 'desc')
    //             ->limit($limit)
    //             ->get();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Related posts retrieved successfully',
    //             'data' => $relatedPosts
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to retrieve related posts',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Get post by slug
     */
    // public function getPostBySlug(Request $request, string $slug): JsonResponse
    // {
    //     try {
    //         $post = Post::with(['category:id,name,slug,color', 'author:id,name,avatar'])
    //             ->published()
    //             ->where('slug', $slug)
    //             ->select([
    //                 'id', 'title', 'slug', 'excerpt', 'content', 'featured_image',
    //                 'published_at', 'views_count', 'likes_count', 'comments_count',
    //                 'category_id', 'user_id'
    //             ])
    //             ->firstOrFail();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Post retrieved successfully',
    //             'data' => $post
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false, 
    //             'message' => 'Failed to retrieve post',
    //             'error' => $e->getMessage()
    //         ], 404);
    //     }
    // }

    /**
     * Get related posts by post slug
     */
    public function getRelatedPosts(Request $request, string $slug): JsonResponse
    {
        try {
            $post = Post::where('slug', $slug)->firstOrFail();
            
            $related = Post::with(['category:id,name,slug,color', 'author:id,name,avatar'])
                ->published()
                ->where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->select([
                    'id', 'title', 'slug', 'excerpt', 'featured_image',
                    'published_at', 'views_count', 'likes_count', 'comments_count',
                    'category_id', 'user_id'
                ])
                ->limit(3)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Related posts retrieved successfully',
                'data' => $related
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve related posts',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get post by slug
     */
    public function getPostBySlug(Request $request, string $slug): JsonResponse
    {
        try {
            $post = Post::with(['category:id,name,slug,color', 'author:id,name,avatar'])
                ->published()
                ->where('slug', $slug)
                ->select([
                    'id', 'title', 'slug', 'excerpt', 'content', 'featured_image',
                    'published_at', 'views_count', 'likes_count', 'comments_count',
                    'category_id', 'user_id'
                ])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Post retrieved successfully',
                'data' => $post
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to retrieve post',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
