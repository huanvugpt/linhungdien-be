<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('youtube_url'); // Link YouTube
            $table->string('youtube_id'); // YouTube video ID (extracted from URL)
            $table->string('thumbnail_url')->nullable(); // YouTube thumbnail
            $table->string('duration')->nullable(); // Duration as string (e.g., "45:30")
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('tags')->nullable(); // Tags as JSON array
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'is_featured']);
            $table->index(['is_active', 'created_at']);
            $table->index(['youtube_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
