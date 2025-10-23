<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'cover_image',
        'is_active',
        'sort_order',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($album) {
            if (empty($album->slug)) {
                $album->slug = Str::slug($album->title);
                
                // Ensure unique slug
                $originalSlug = $album->slug;
                $count = 1;
                while (static::where('slug', $album->slug)->exists()) {
                    $album->slug = $originalSlug . '-' . $count++;
                }
            }
        });
    }

    /**
     * Get the admin who created this album
     */
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get all images in this album
     */
    public function images()
    {
        return $this->hasMany(AlbumImage::class)->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * Get featured images
     */
    public function featuredImages()
    {
        return $this->hasMany(AlbumImage::class)->where('is_featured', true)->orderBy('sort_order');
    }

    /**
     * Get the cover image or first image
     */
    public function getCoverImageAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        $firstImage = $this->images()->first();
        return $firstImage ? $firstImage->path : null;
    }

    /**
     * Scope for active albums
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get albums ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    /**
     * Get total images count
     */
    public function getImagesCountAttribute()
    {
        return $this->images()->count();
    }

    /**
     * Get album URL
     */
    public function getUrlAttribute()
    {
        return url("/albums/{$this->slug}");
    }
}