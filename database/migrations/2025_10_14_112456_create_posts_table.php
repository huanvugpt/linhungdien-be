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
        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('excerpt')->nullable();
                $table->longText('content');
                $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable(); // Array of images
            $table->enum('status', ['draft', 'pending', 'published', 'rejected'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            
            // Relationships
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('user_id'); // Author
            $table->unsignedBigInteger('admin_id')->nullable(); // Approver
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status']);
            $table->index('is_featured');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
