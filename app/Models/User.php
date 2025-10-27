<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email', 
        'password',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'avatar',
        'status',
        'first_login',
        'google_id',
        'facebook_id',
        'provider',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url'
    ];

    /**
     * Get the full URL for the avatar.
     */
    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return null;
        }
        
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3333');
        return $frontendUrl . '/storage/' . $this->avatar;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved_at' => 'datetime',
            'date_of_birth' => 'date',
            'first_login' => 'boolean',
        ];
    }

    /**
     * Check if user is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if user is pending approval
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if user is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get the admin who approved this user
     */
    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'approved_by');
    }

    /**
     * Check if this is user's first login
     */
    public function isFirstLogin(): bool
    {
        return $this->first_login;
    }

    /**
     * Get the posts created by the user
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the post views by the user
     */
    public function postViews()
    {
        return $this->hasMany(PostView::class);
    }

    /**
     * Get the post likes by the user
     */
    public function postLikes()
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Get the posts liked by the user
     */
    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_likes')->withTimestamps();
    }

    /**
     * Get the post submissions by the user
     */
    public function postSubmissions()
    {
        return $this->hasMany(PostSubmission::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        // You can implement this based on your admin logic
        // For example, check if user has admin role or specific status
        return $this->status === 'admin' || $this->hasRole('admin');
    }

    /**
     * Check if user has specific role (placeholder method)
     */
    public function hasRole(string $role): bool
    {
        // Implement role checking logic here
        // This is a placeholder - you might have a roles table or role field
        return false; // Temporarily return false
    }
}
