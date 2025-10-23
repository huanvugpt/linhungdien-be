<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
    ];

    /**
     * Get the post that owns the like.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user that owns the like.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
