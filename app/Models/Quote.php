<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
    ];

    /**
     * Get random quotes that are different from each other
     */
    public static function getRandomQuotes($count = 5)
    {
        return static::inRandomOrder()->limit($count)->get();
    }

    /**
     * Get random unique quotes (no duplicates in content)
     */
    public static function getUniqueRandomQuotes($count = 5)
    {
        return static::select('content')
            ->distinct()
            ->inRandomOrder()
            ->limit($count)
            ->pluck('content');
    }
}
