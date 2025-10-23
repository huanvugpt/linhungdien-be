<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users and categories
        $users = User::all();
        $categories = Category::all();

        // Create 20 sample posts
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $category = $categories->random();
            $publishedAt = Carbon::now()->subDays(rand(0, 30));

            Post::create([
                'title' => "Sample Post " . ($i + 1),
                'slug' => "sample-post-" . ($i + 1),
                'excerpt' => "This is a sample excerpt for post " . ($i + 1),
                'content' => "This is sample content for post " . ($i + 1) . ". It includes detailed information about the topic.",
                'featured_image' => "sample-image-" . ($i + 1) . ".jpg",
                'status' => 'published',
                'is_featured' => rand(0, 5) === 0, // 20% chance of being featured
                'allow_comments' => true,
                'published_at' => $publishedAt,
                'views_count' => rand(0, 1000),
                'likes_count' => rand(0, 100),
                'comments_count' => rand(0, 50),
                'category_id' => $category->id,
                'user_id' => $user->id,
            ]);
        }
    }
}