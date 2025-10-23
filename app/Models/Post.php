<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'gallery',
        'status',
        'is_featured',
        'allow_comments',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'views_count',
        'likes_count',
        'comments_count',
        'category_id',
        'user_id',
        'admin_id',
        'approved_at',
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'published_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'featured_image_url'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now());
    }

    /**
     * Scope a query to only include featured posts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to order by most recent posts.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Get the user who authored the post.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the category that the post belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the full URL for the featured image.
     */
    public function getFeaturedImageUrlAttribute()
    {
        if (!$this->featured_image) {
            return null;
        }
        
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3333');
        return $frontendUrl . '/storage/' . $this->featured_image;
    }

    /**
     * Get the full URLs for gallery images.
     */
    protected function getGalleryAttribute($value)
    {
        if (!$value) {
            return null;
        }

        $gallery = json_decode($value, true);
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3333');
        
        return array_map(function($image) use ($frontendUrl) {
            return $frontendUrl . '/storage/' . $image;
        }, $gallery);
    }


    /**
     * Get the admin that approved the post.
     */
    public function approver()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Get the views for the post.
     */
    public function views()
    {
        return $this->hasMany(PostView::class);
    }

    /**
     * Get the likes for the post.
     */
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Scope a query to order by most viewed.
     */
    public function scopeMostViewed($query, $limit = null)
    {
        $query = $query->orderBy('views_count', 'desc');
        
        if ($limit) {
            $query = $query->limit($limit);
        }
        
        return $query;
    }

    /**
     * Check if post is published.
     */
    public function isPublished()
    {
        return $this->status === 'published';
    }

    /**
     * Check if post is pending approval.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if post is draft.
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Check if post is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Get formatted published date.
     */
    public function getFormattedPublishedDateAttribute()
    {
        return $this->published_at ? $this->published_at->format('d/m/Y H:i') : null;
    }

    /**
     * Get excerpt or truncated content.
     */
    public function getExcerptAttribute($value)
    {
        return $value ?: Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Increment views count
     */
    public function incrementViews($userId = null, $ipAddress = null, $userAgent = null)
    {
        // Check if view already exists for this user/IP today
        $today = Carbon::today();
        $existingView = $this->views()
            ->where(function ($query) use ($userId, $ipAddress) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('ip_address', $ipAddress);
                }
            })
            ->whereDate('viewed_at', $today)
            ->exists();

        if (!$existingView) {
            $this->views()->create([
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'viewed_at' => now(),
            ]);

            $this->increment('views_count');
        }
    }

    /**
     * Toggle like for user
     */
    public function toggleLike($userId)
    {
        $like = $this->likes()->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            $this->decrement('likes_count');
            return false; // unliked
        } else {
            $this->likes()->create(['user_id' => $userId]);
            $this->increment('likes_count');
            return true; // liked
        }
    }

    /**
     * Check if user has liked the post
     */
    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
