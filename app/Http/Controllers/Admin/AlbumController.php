<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Album;
use App\Models\AlbumImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    /**
     * Display a listing of albums
     */
    public function index(Request $request)
    {
        $query = Album::with(['creator', 'images'])
                     ->withCount('images')
                     ->ordered();

        // Search functionality
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        $albums = $query->paginate(15);

        return view('admin.albums.index', compact('albums'));
    }

    /**
     * Show the form for creating a new album
     */
    public function create()
    {
        return view('admin.albums.create');
    }

    /**
     * Store a newly created album
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $album = Album::create([
            'title' => $request->title,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
            'created_by' => Auth::guard('admin')->id(),
        ]);

        return redirect()
            ->route('admin.albums.show', $album)
            ->with('success', 'Album created successfully!');
    }

    /**
     * Display the specified album with images
     */
    public function show(Album $album)
    {
        $album->load(['images.uploader', 'creator']);
        
        return view('admin.albums.show', compact('album'));
    }

    /**
     * Show the form for editing the specified album
     */
    public function edit(Album $album)
    {
        return view('admin.albums.edit', compact('album'));
    }

    /**
     * Update the specified album
     */
    public function update(Request $request, Album $album)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // If title changed, regenerate slug
        if ($album->title !== $request->title) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $count = 1;
            while (Album::where('slug', $slug)->where('id', '!=', $album->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $album->slug = $slug;
        }

        $album->update([
            'title' => $request->title,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()
            ->route('admin.albums.show', $album)
            ->with('success', 'Album updated successfully!');
    }

    /**
     * Remove the specified album
     */
    public function destroy(Album $album)
    {
        // Delete all images first (this will trigger file deletion)
        $album->images()->delete();
        
        // Delete the album
        $album->delete();

        return redirect()
            ->route('admin.albums.index')
            ->with('success', 'Album deleted successfully!');
    }

    /**
     * Upload images to album
     */
    public function uploadImages(Request $request, Album $album)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $uploadedCount = 0;
        $errors = [];

        foreach ($request->file('images') as $file) {
            try {
                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . Str::random(10) . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                
                // Create album directory path
                $albumDir = 'albums/' . ($album->slug ?: $album->id);
                
                // Store the file in public disk
                $storedPath = $file->storeAs($albumDir, $filename, 'public');
                
                if (!$storedPath) {
                    throw new \Exception('Failed to store file');
                }

                // Get image dimensions
                $dimensions = null;
                try {
                    $imageSize = getimagesize($file->getPathname());
                    if ($imageSize) {
                        $dimensions = [
                            'width' => $imageSize[0],
                            'height' => $imageSize[1]
                        ];
                    }
                } catch (\Exception $e) {
                    // Ignore dimension errors, not critical
                }

                // Create album image record
                $albumImage = AlbumImage::create([
                    'album_id' => $album->id,
                    'title' => pathinfo($originalName, PATHINFO_FILENAME),
                    'filename' => $originalName,
                    'path' => $storedPath,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'dimensions' => $dimensions, // Laravel will auto cast to JSON
                    'sort_order' => AlbumImage::where('album_id', $album->id)->max('sort_order') + 1,
                    'uploaded_by' => Auth::guard('admin')->id(),
                ]);

                $uploadedCount++;

            } catch (\Exception $e) {
                $errors[] = "Failed to upload {$file->getClientOriginalName()}: " . $e->getMessage();
                \Log::error('Album image upload failed', [
                    'album_id' => $album->id,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($uploadedCount > 0) {
            $message = "Successfully uploaded {$uploadedCount} image(s)";
            if (!empty($errors)) {
                $message .= ". Some files had errors: " . implode(' | ', $errors);
                return back()->with('warning', $message);
            }
            return back()->with('success', $message);
        } else {
            return back()->with('error', 'No images were uploaded. Errors: ' . implode(' | ', $errors));
        }
    }

    /**
     * Delete a specific image
     */
    public function deleteImage(Album $album, AlbumImage $image)
    {
        if ($image->album_id !== $album->id) {
            return back()->with('error', 'Image not found in this album');
        }

        $image->delete(); // This will trigger file deletion via model boot method

        return back()->with('success', 'Image deleted successfully!');
    }

    /**
     * Update image details
     */
    public function updateImage(Request $request, Album $album, AlbumImage $image)
    {
        if ($image->album_id !== $album->id) {
            return back()->with('error', 'Image not found in this album');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $image->update([
            'title' => $request->title,
            'description' => $request->description,
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => $request->sort_order ?? $image->sort_order,
        ]);

        return back()->with('success', 'Image updated successfully!');
    }

    /**
     * Set album cover image
     */
    public function setCover(Album $album, AlbumImage $image)
    {
        if ($image->album_id !== $album->id) {
            return back()->with('error', 'Image not found in this album');
        }

        $album->update(['cover_image' => $image->path]);

        return back()->with('success', 'Cover image updated successfully!');
    }
}
