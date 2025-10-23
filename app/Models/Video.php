<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug', 
        'description',
        'youtube_url',
        'youtube_id',
        'thumbnail_url',
        'duration',
        'is_featured',
        'is_active',
        'views_count',
        'likes_count',
        'category_id',
        'user_id',
        'published_at'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'published_at'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($video) {
            // Auto-generate slug if not provided
            if (!$video->slug) {
                $video->slug = \Str::slug($video->title);
            }

            // Extract YouTube ID and generate thumbnail
            if ($video->youtube_url && !$video->youtube_id) {
                $video->youtube_id = $video->extractYouTubeId($video->youtube_url);
            }

            if ($video->youtube_id && !$video->thumbnail_url) {
                $video->thumbnail_url = $video->getYouTubeThumbnail($video->youtube_id);
            }
        });

        static::updating(function ($video) {
            // Update YouTube data if URL changed
            if ($video->isDirty('youtube_url') && $video->youtube_url) {
                $video->youtube_id = $video->extractYouTubeId($video->youtube_url);
                $video->thumbnail_url = $video->getYouTubeThumbnail($video->youtube_id);
            }
        });
    }

    /**
     * Relationship: Video belongs to a category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship: Video belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Only active videos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Only featured videos
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Order by most recent
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('published_at', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Order by most viewed
     */
    public function scopeMostViewed(Builder $query): Builder
    {
        return $query->orderBy('views_count', 'desc');
    }

    /**
     * Scope: Published videos only
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published_at', '<=', now());
    }

    /**
     * Extract YouTube video ID from various URL formats
     */
    public function extractYouTubeId($url): ?string
    {
        if (!$url) return null;

        // Handle different YouTube URL formats
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Get YouTube thumbnail URL
     */
    public function getYouTubeThumbnail($videoId): ?string
    {
        if (!$videoId) return null;
        
        // Use maxresdefault for highest quality, fallback to hqdefault
        return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
    }

    /**
     * Get YouTube embed URL
     */
    public function getYouTubeEmbedUrl(): ?string
    {
        if (!$this->youtube_id) return null;
        
        return "https://www.youtube.com/embed/{$this->youtube_id}";
    }

    /**
     * Get YouTube watch URL
     */
    public function getYouTubeWatchUrl(): ?string
    {
        if (!$this->youtube_id) return null;
        
        return "https://www.youtube.com/watch?v={$this->youtube_id}";
    }

    /**
     * Increment views count
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Format duration for display
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) return '00:00';
        
        // If duration is in seconds
        if (is_numeric($this->duration)) {
            $minutes = floor($this->duration / 60);
            $seconds = $this->duration % 60;
            return sprintf('%02d:%02d', $minutes, $seconds);
        }
        
        // If duration is already formatted (e.g., "45:30")
        return $this->duration;
    }

    /**
     * Check if video is recently published (within last 7 days)
     */
    public function getIsRecentAttribute(): bool
    {
        return $this->published_at && $this->published_at->diffInDays() <= 7;
    }

    /**
     * Get readable published date
     */
    public function getPublishedDateAttribute(): string
    {
        if (!$this->published_at) return 'Chưa xuất bản';
        
        return $this->published_at->locale('vi')->diffForHumans();
    }

    /**
     * Get video statistics
     */
    public function getStatsAttribute(): array
    {
        return [
            'views' => $this->views_count ?? 0,
            'likes' => $this->likes_count ?? 0,
            'duration' => $this->formatted_duration,
            'published' => $this->published_date
        ];
    }
}