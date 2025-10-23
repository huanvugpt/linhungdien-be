<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class AlbumImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id',
        'title',
        'description',
        'filename',
        'path',
        'mime_type',
        'file_size',
        'dimensions',
        'sort_order',
        'is_featured',
        'uploaded_by'
    ];

    protected $casts = [
        'dimensions' => 'array',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the album this image belongs to
     */
    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Get the admin who uploaded this image
     */
    public function uploader()
    {
        return $this->belongsTo(Admin::class, 'uploaded_by');
    }

    /**
     * Get the full URL of the image
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }

    /**
     * Get thumbnail URL (if you implement thumbnails)
     */
    public function getThumbnailUrlAttribute()
    {
        $pathParts = pathinfo($this->path);
        $thumbnailPath = $pathParts['dirname'] . '/thumbs/' . $pathParts['basename'];
        
        if (Storage::exists($thumbnailPath)) {
            return Storage::url($thumbnailPath);
        }
        
        return $this->url; // Fallback to original image
    }

    /**
     * Get human readable file size
     */
    public function getHumanFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes >= 1024 && $i < 3; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get image dimensions as string
     */
    public function getDimensionsStringAttribute()
    {
        if (!$this->dimensions) {
            return 'Unknown';
        }
        
        return $this->dimensions['width'] . ' × ' . $this->dimensions['height'];
    }

    /**
     * Scope for featured images
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for ordering by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * Delete the file when model is deleted
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($image) {
            if (Storage::exists($image->path)) {
                Storage::delete($image->path);
            }
            
            // Also delete thumbnail if exists
            $pathParts = pathinfo($image->path);
            $thumbnailPath = $pathParts['dirname'] . '/thumbs/' . $pathParts['basename'];
            if (Storage::exists($thumbnailPath)) {
                Storage::delete($thumbnailPath);
            }
        });
    }
}