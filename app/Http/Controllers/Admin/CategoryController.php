<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('sort_order')->paginate(10);
        
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

        /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:categories',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'color' => 'nullable|string|max:7',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            // Auto-generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('categories', 'public');
                $validated['image'] = $imagePath;
            }

            // Set default values
            $validated['is_active'] = $request->has('is_active');
            $validated['sort_order'] = $validated['sort_order'] ?? 0;
            $validated['color'] = $validated['color'] ?? '#007bff';

            $category = Category::create($validated);

            if (!$category) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Không thể tạo category. Vui lòng thử lại.');
            }

            return redirect()->route('admin.categories.index')
                            ->with('success', 'Category đã được tạo thành công!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.');

        } catch (\Exception $e) {
            \Log::error('Category creation failed: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo category: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $posts = $category->posts()->latest()->paginate(10);
        return view('admin.categories.show', compact('category', 'posts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        try {
            // Debug: log request data
            \Log::info('Category update request data:', $request->all());
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
                'description' => 'nullable|string',
                'color' => 'nullable|string|max:7',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'is_active' => 'sometimes|boolean',
                'sort_order' => 'nullable|integer',
            ]);

            // Auto generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($category->image && \Storage::disk('public')->exists($category->image)) {
                    \Storage::disk('public')->delete($category->image);
                }
                
                $imagePath = $request->file('image')->store('categories', 'public');
                $validated['image'] = $imagePath;
            }

            // Set default values
            $validated['is_active'] = $request->has('is_active');
            $validated['sort_order'] = $validated['sort_order'] ?? 0;
            $validated['color'] = $validated['color'] ?? '#007bff';

            $result = $category->update($validated);

            if (!$result) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Không thể cập nhật category. Vui lòng thử lại.');
            }

            return redirect()->route('admin.categories.index')
                            ->with('success', 'Category đã được cập nhật thành công!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Category update validation failed:', [
                'errors' => $e->validator->errors()->toArray(),
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Dữ liệu không hợp lệ. Vui lòng kiểm tra các trường bắt buộc.');

        } catch (\Exception $e) {
            \Log::error('Category update failed: ' . $e->getMessage(), [
                'category_id' => $category->id,
                'request' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            // Check if category has posts
            $postsCount = $category->posts()->count();
            if ($postsCount > 0) {
                return redirect()->route('admin.categories.index')
                                ->with('error', "Không thể xóa category này vì có {$postsCount} bài viết đang sử dụng.");
            }

            // Delete category image if exists
            if ($category->image && \Storage::disk('public')->exists($category->image)) {
                \Storage::disk('public')->delete($category->image);
            }

            $categoryName = $category->name;
            $result = $category->delete();

            if (!$result) {
                return redirect()->route('admin.categories.index')
                    ->with('error', 'Không thể xóa category. Vui lòng thử lại.');
            }

            return redirect()->route('admin.categories.index')
                            ->with('success', "Category '{$categoryName}' đã được xóa thành công!");

        } catch (\Exception $e) {
            \Log::error('Category deletion failed: ' . $e->getMessage(), [
                'category_id' => $category->id,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('admin.categories.index')
                ->with('error', 'Có lỗi xảy ra khi xóa category: ' . $e->getMessage());
        }
    }

    /**
     * Toggle category status
     */
    public function toggle(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Category {$status} successfully.");
    }
}
