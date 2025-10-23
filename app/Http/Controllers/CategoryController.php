<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories for API
     */
    public function getCategories()
    {
        $categories = Category::where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories
            ]
        ]);
    }

    /**
     * Get category by slug for API
     */
    public function getCategory($slug)
    {
        $category = Category::where('slug', $slug)
                          ->where('is_active', true)
                          ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Get posts by category for API
     */
    public function getCategoryPosts($slug, Request $request)
    {
        $category = Category::where('slug', $slug)
                          ->where('is_active', true)
                          ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $query = Post::with(['category', 'author'])
                    ->published()
                    ->where('category_id', $category->id)
                    ->orderBy('is_featured', 'desc')
                    ->orderBy('published_at', 'desc');

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => [
                'posts' => $posts->items(),
                'category' => $category,
                'pagination' => [
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                ]
            ]
        ]);
    }
}